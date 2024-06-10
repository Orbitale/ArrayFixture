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

namespace Tests\Orbitale\Component\ArrayFixture\Stubs;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;

class ObjectManagerStub implements ObjectManager
{
    /** @var ClassMetadata */
    private $metadata;

    /** @var array */
    private $persisted = [];

    /** @var int */
    private $flushed = 0;

    public function __construct(ClassMetadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getPersisted(): array
    {
        return $this->persisted;
    }

    public function getFlushed(): int
    {
        return $this->flushed;
    }

    public function persist($object): void
    {
        $this->persisted[] = $object;
    }

    public function flush(): int
    {
        return $this->flushed++;
    }

    public function getClassMetadata($className): ClassMetadata
    {
        return $this->metadata;
    }

    public function find(string $className, $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?object
    {
    }

    public function remove(object $object)
    {
    }

    public function clear()
    {
    }

    public function detach(object $object)
    {
    }

    public function refresh(object $object, LockMode|int|null $lockMode = null): void
    {
    }

    public function getRepository(string $className): EntityRepository
    {
    }

    public function getMetadataFactory(): ClassMetadataFactory
    {
    }

    public function initializeObject(object $obj)
    {
    }

    public function contains(object $object)
    {
    }
}
