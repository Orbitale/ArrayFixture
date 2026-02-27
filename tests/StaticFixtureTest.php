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

namespace Tests\Orbitale\Component\ArrayFixture;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Orbitale\Component\ArrayFixture\Ref;
use RuntimeException;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\StaticLoadablePostFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\StaticPostFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\StaticTimestampedPostFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\PostStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\ReferenceRepositoryStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\TimestampedPostStub;

class StaticFixtureTest extends TestCase
{
    public function test_findByKeyAndValue_with_scalar_value(): void
    {
        $result = StaticPostFixtureStub::findByKeyAndValue('title', 'Second title');

        self::assertSame('Second post', $result['name']);
        self::assertSame('Second title', $result['title']);
    }

    public function test_findByKeyAndValue_throws_when_not_found(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('No fixture object with key "%s" and value "%s" in class "%s".', 'title', 'Nonexistent title', StaticPostFixtureStub::class));

        StaticPostFixtureStub::findByKeyAndValue('title', 'Nonexistent title');
    }

    public function test_findByKeyAndValue_with_Ref_value(): void
    {
        $ref = new Ref(PostStub::class, 'parent-ref');

        $result = StaticPostFixtureStub::findByKeyAndValue('parent', $ref);

        self::assertSame('Second post', $result['name']);
    }

    public function test_findByKeyAndValue_returns_first_match(): void
    {
        $result = StaticPostFixtureStub::findByKeyAndValue('title', 'First title');

        self::assertSame('First post', $result['name']);
    }

    public function test_filterByKeyAndValue_with_scalar_value(): void
    {
        $results = StaticPostFixtureStub::filterByKeyAndValue('title', 'First title');

        self::assertCount(2, $results);
        self::assertArrayHasKey('post-1', $results);
        self::assertArrayHasKey('post-3', $results);
    }

    public function test_filterByKeyAndValue_with_Ref_value(): void
    {
        $ref = new Ref(PostStub::class, 'parent-ref');

        $results = StaticPostFixtureStub::filterByKeyAndValue('parent', $ref);

        self::assertCount(1, $results);
        self::assertArrayHasKey('post-2', $results);
        self::assertSame('parent-ref', $results['post-2']['parent']->name);
    }

    public function test_filterByKeyAndValue_returns_empty_when_no_match(): void
    {
        $results = StaticPostFixtureStub::filterByKeyAndValue('title', 'Nonexistent');

        self::assertCount(0, $results);
    }

    public function test_filterData_with_closure(): void
    {
        $results = StaticPostFixtureStub::filterData(static fn (array $data) => \count($data['tags']) > 1);

        self::assertCount(1, $results);
        self::assertArrayHasKey('post-1', $results);
    }

    public function test_getIdFromName_returns_id(): void
    {
        $id = StaticPostFixtureStub::getIdFromName('Second post');

        self::assertSame('post-2', $id);
    }

    public function test_getIdFromName_throws_when_not_found(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('No fixture object with name "%s" in class "%s".', 'Nonexistent post', StaticPostFixtureStub::class));

        StaticPostFixtureStub::getIdFromName('Nonexistent post');
    }

    public function test_load_persists_entities_with_correct_data(): void
    {
        $manager = $this->getObjectManager();

        $parentObject = (new \ReflectionClass(PostStub::class))->newInstanceWithoutConstructor();
        $tagRef1 = (new \ReflectionClass(PostStub::class))->newInstanceWithoutConstructor();
        $tagRef2 = (new \ReflectionClass(PostStub::class))->newInstanceWithoutConstructor();

        $refsRepo = new ReferenceRepositoryStub($manager);
        $refsRepo->addReference('parent-ref', $parentObject);
        $refsRepo->addReference('tag-ref-1', $tagRef1);
        $refsRepo->addReference('tag-ref-2', $tagRef2);

        /** @var PostStub[] $persistedStubs */
        $persistedStubs = [];

        $manager->expects($this->once())->method('flush');
        $manager->expects($this->exactly(2))->method('persist')->with(
            new Callback(function ($stub) use (&$persistedStubs) {
                $result = \is_object($stub) && $stub instanceof PostStub;
                if ($result) {
                    $persistedStubs[] = $stub;
                }
                return $result;
            }),
        );

        $fixture = new StaticLoadablePostFixtureStub();
        $fixture->setReferenceRepository($refsRepo);
        $fixture->load($manager);

        // Post 1: basic scalar data
        self::assertSame('post-1', $this->getProperty($persistedStubs[0], 'id'));
        self::assertSame('Basic title', $persistedStubs[0]->getTitle());
        self::assertSame(['php'], $persistedStubs[0]->getTags());
        self::assertNull($persistedStubs[0]->getParent());

        // Post 2: Closure evaluation, Ref resolution, array-with-Refs → ArrayCollection
        self::assertSame('post-2', $this->getProperty($persistedStubs[1], 'id'));
        self::assertSame('Closure title', $persistedStubs[1]->getTitle());
        self::assertSame($parentObject, $persistedStubs[1]->getParent());

        $tags = $this->getProperty($persistedStubs[1], 'tags');
        self::assertInstanceOf(ArrayCollection::class, $tags);
        self::assertSame($tagRef1, $tags[0]);
        self::assertSame($tagRef2, $tags[1]);
    }

    public function test_load_auto_populates_createdAt_and_updatedAt(): void
    {
        $manager = $this->getObjectManager();

        /** @var TimestampedPostStub[] $persistedStubs */
        $persistedStubs = [];

        $manager->expects($this->once())->method('flush');
        $manager->expects($this->once())->method('persist')->with(
            new Callback(function ($stub) use (&$persistedStubs) {
                $result = \is_object($stub) && $stub instanceof TimestampedPostStub;
                if ($result) {
                    $persistedStubs[] = $stub;
                }
                return $result;
            }),
        );

        $fixture = new StaticTimestampedPostFixtureStub();
        $fixture->setReferenceRepository(new ReferenceRepositoryStub($manager));
        $fixture->load($manager);

        self::assertSame('Timestamped post', $persistedStubs[0]->getTitle());
        self::assertInstanceOf(\DateTime::class, $persistedStubs[0]->getCreatedAt());
        self::assertInstanceOf(\DateTime::class, $persistedStubs[0]->getUpdatedAt());
    }

    private function getProperty(object $object, string $property): mixed
    {
        return (new \ReflectionProperty($object, $property))->getValue($object);
    }

    /**
     * @return ObjectManager&MockObject
     */
    private function getObjectManager(): ObjectManager
    {
        $metadata = $this->createMock(ORMClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']);

        $om = $this->createMock(ObjectManager::class);
        $om->method('getClassMetadata')->willReturn($metadata);

        return $om;
    }
}
