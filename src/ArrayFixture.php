<?php

declare(strict_types=1);

/*
 * This file is part of the Orbitale ArrayFixture package.
 *
 * (c) Alexandre Rock Ancelet <alex@orbitale.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orbitale\Component\ArrayFixture;

use function count;
use function method_exists;
use function property_exists;
use function sprintf;
use Closure;
use Doctrine\Common\DataFixtures\AbstractFixture as BaseAbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Instantiator\Instantiator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Generator;
use InvalidArgumentException;
use ReflectionMethod;
use RuntimeException;

/**
 * When used alongside with Doctrine FixturesBundle,
 * you may need to implement the ORMFixtureInterface interface too.
 */
abstract class ArrayFixture extends BaseAbstractFixture
{
    /** @var ObjectManager */
    private $manager;

    /** @var int */
    private $numberOfIteratedObjects = 0;

    /** @var bool */
    private $clearEMOnFlush;

    /** @var null|Closure */
    private $setter;

    /** @var null|Instantiator */
    private static $instantiator;

    public function __construct()
    {
        $this->clearEMOnFlush = $this->clearEntityManagerOnFlush();

        if (!($this instanceof OrderedFixtureInterface) && (new ReflectionMethod($this, 'getOrder'))->getDeclaringClass()->getName() !== self::class) {
            @trigger_error(\sprintf(
                "The \"%s\" method is overridden on the \"%s\" class, but it does not implement the \"%s\" interface.\n".
                'ArrayFixture stopped implementing this interface in v1.1.0 to ensure compatibility with the "%s" interface.'.
                'If you want ordered fixtures, you should implement either of these interfaces manually in your code.',
                'getOrder',
                static::class,
                OrderedFixtureInterface::class,
                DependentFixtureInterface::class
            ), E_USER_DEPRECATED);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        if ($this->manager instanceof EntityManagerInterface && $this->disableLogger()) {
            $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);
        }

        $objects = $this->getObjects();

        $this->numberOfIteratedObjects = 0;
        foreach ($objects as $data) {
            $this->numberOfIteratedObjects++;
            $this->fixtureObject($data);
        }

        // Flush if we performed a "whole" fixture load,
        //  or if we flushed with batches but have not flushed all items.
        $this->manager->flush();
        if ($this->clearEMOnFlush) {
            $this->manager->clear();
        }
    }

    /**
     * @deprecated
     */
    public function getOrder(): int
    {
        @trigger_error(\sprintf(
            "The \"%s\" method is deprecated since v1.1.0 and will be removed in v2.0, as the \"%s\" interface conflicts with \"%s\".\n".
            'You should implement it in your own class instead',
            __METHOD__,
            OrderedFixtureInterface::class,
            DependentFixtureInterface::class
        ), E_USER_DEPRECATED);

        return 0;
    }

    /**
     * Returns the class of the entity you're managing.
     */
    abstract protected function getEntityClass(): string;

    /**
     * Returns an iterable containing the list of objects that should be persisted.
     *
     * @return array[]|Generator
     */
    abstract protected function getObjects(): iterable;

    /**
     * If true, the SQL logger will be disabled, and therefore will avoid memory leaks and save memory during execution.
     * Very useful for big batches of entities.
     */
    protected function disableLogger(): bool
    {
        return true;
    }

    /**
     * Returns the prefix used to create fixtures reference.
     * If returns `null`, no reference will be created for the object.
     * NOTE: To create references of an object, it must have an ID, and if not, implement __toString(), because
     *   each object is referenced BEFORE flushing the database.
     * NOTE2: If you specified a "flushEveryXIterations" value, then the object will be provided with an ID every time.
     */
    protected function getReferencePrefix(): ?string
    {
        return null;
    }

    /**
     * When set, you can customize the method that will be used
     * to determine the second part of the reference prefix.
     * For example, if reference prefix is "my-entity-" and the
     * method is "getIdentifier()", the reference will be:
     * "$reference = 'my-entity-'.$obj->getIdentifier()".
     *
     * Only used when getReferencePrefix() returns non-empty value.
     *
     * Always tries to fall back to "__toString()".
     */
    protected function getMethodNameForReference(): string
    {
        return 'getId';
    }

    /**
     * If specified, the entity manager will be flushed every X times, depending on your specified values.
     * Default is null, so the database is flushed only at the end of all persists.
     */
    protected function flushEveryXIterations(): int
    {
        return 0;
    }

    /**
     * If true, will run $em->clear() after having run $em->flush().
     * This allows saving some memory when using huge sets of non-referenced fixtures.
     */
    protected function clearEntityManagerOnFlush(): bool
    {
        return true;
    }

    /**
     * Creates a new instance of the class associated with the fixture.
     * Override this method if you have constructor arguments to manage yourself depending on input data.
     *
     * @param mixed[] $data
     */
    protected function createNewInstance(array $data): object
    {
        $entityClass = $this->getEntityClass();
        $instance = self::getInstantiator()->instantiate($entityClass);

        $setter = $this->getSetter();

        foreach ($data as $key => $value) {
            if ($value instanceof Closure) {
                // Allow specifying values as closures in order to customize field population.
                $value = $value($instance, $this, $this->manager);
            }

            $setter->bindTo($instance, $entityClass)($key, $value);
        }

        return $instance;
    }

    /**
     * Creates the object and persist it in database.
     *
     * @param mixed[] $data
     */
    private function fixtureObject(array $data): void
    {
        $obj = $this->createNewInstance($data);

        // Sometimes, primary keys are specified in fixtures.
        // We must make sure Doctrine will force them to be saved.
        // Support for non-composite primary keys only.
        // /!\ Be careful, this will override the generator type for ALL objects of the same entity class!
        //     This means that it _may_ break objects for which ids are not provided in the fixtures.
        // The solution for the user: don't specify any ID, or specify ALL of them.
        /** @var ClassMetadata $metadata */
        $metadata = $this->manager->getClassMetadata($this->getEntityClass());
        $primaryKey = $metadata->getIdentifierFieldNames();
        if (1 === count($primaryKey) && isset($data[$primaryKey[0]])) {
            $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new AssignedGenerator());
        }

        $this->manager->persist($obj);

        if (
            $this->flushEveryXIterations() > 0
            && 0 === $this->numberOfIteratedObjects % $this->flushEveryXIterations()
        ) {
            $this->manager->flush();
            if ($this->clearEMOnFlush) {
                $this->manager->clear();
            }
        }

        // If we have to add a reference, we do it
        if ($prefix = $this->getReferencePrefix()) {
            $methodName = $this->getMethodNameForReference();

            $reference = null;

            if (method_exists($obj, $methodName)) {
                $reference = $obj->{$methodName}();
            } elseif (method_exists($obj, '__toString')) {
                $reference = (string) $obj;
            }

            if (!$reference) {
                throw new RuntimeException(
                    sprintf(
                        'If you want to specify a reference with prefix "%s", method "%s" or "%s" must exist in the class, or you can override the "%s" method and add your own.',
                        $prefix,
                        $methodName,
                        '__toString()',
                        'getMethodNameForReference'
                    )
                );
            }
            $this->addReference($prefix.$reference, $obj);
        }
    }

    private function getSetter(): Closure
    {
        if (!$this->setter) {
            $class = $this->getEntityClass();

            return $this->setter = function (string $property, $value) use ($class): void {
                if (!property_exists($this, $property)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Cannot set property "%s" to "%s" object since this property does not exist.',
                            $property,
                            $class
                        )
                    );
                }

                $this->{$property} = $value;
            };
        }

        return $this->setter;
    }

    private static function getInstantiator(): Instantiator
    {
        if (!self::$instantiator) {
            return self::$instantiator = new Instantiator();
        }

        return self::$instantiator;
    }
}
