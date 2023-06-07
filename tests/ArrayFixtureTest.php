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

use Doctrine\DBAL\Driver;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as ODMClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadata as ClassMetadataInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\CustomNumberOfFlushesFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\InexistentMethodPrefixFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\InexistentPropertyFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\ObjectsWithIdsStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\PostSelfReferenceFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\PostTitleFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\ToStringPrefixFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\DocumentManagerStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\EntityManagerStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\ObjectManagerStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\PostStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\ReferenceRepositoryStub;

class ArrayFixtureTest extends TestCase
{
    public function test post title fixture(): void
    {
        $manager = $this->getObjectManager();

        (new PostTitleFixtureStub())->load($manager);

        self::assertSame(1, $manager->getFlushed());

        $entities = $manager->getPersisted();
        self::assertCount(1, $entities);
        self::assertInstanceOf(PostStub::class, $entities[0]);
        self::assertSame('Default title', $entities[0]->getTitle());
    }

    public function test post self reference fixture(): void
    {
        $manager = $this->getObjectManager();
        $refs = new ReferenceRepositoryStub($manager);

        $fixture = new PostSelfReferenceFixtureStub();
        $fixture->setReferenceRepository($refs);
        $fixture->load($manager);

        self::assertSame(1, $manager->getFlushed());

        $entities = $manager->getPersisted();
        self::assertCount(2, $entities);
        self::assertInstanceOf(PostStub::class, $entities[0]);
        self::assertInstanceOf(PostStub::class, $entities[1]);
        self::assertSame('Default title', $entities[0]->getTitle());
        self::assertSame('Second title', $entities[1]->getTitle());
        self::assertSame($entities[0], $entities[1]->getParent());
    }

    public function test inexistent property(): void
    {
        $manager = $this->getObjectManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot set property "does_not_exist" to "Tests\Orbitale\Component\ArrayFixture\Stubs\PostStub" object since this property does not exist.');

        (new InexistentPropertyFixtureStub())->load($manager);
    }

    public function test toString for prefix(): void
    {
        $manager = $this->getObjectManager();
        $refsRepo = new ReferenceRepositoryStub($manager);

        $fixture = new ToStringPrefixFixtureStub();
        $fixture->setReferenceRepository($refsRepo);
        $fixture->load($manager);

        self::assertSame(1, $manager->getFlushed());

        $entities = $manager->getPersisted();
        self::assertCount(1, $entities);
        self::assertInstanceOf(PostStub::class, $entities[0]);
        self::assertSame('Default title', $entities[0]->getTitle());
        $refs = $refsRepo->getReferences();
        self::assertArrayHasKey('post-Default title', $refs);
        self::assertSame($refs['post-Default title'], $entities[0]);
    }

    public function test inexistent prefix method(): void
    {
        $manager = $this->getObjectManager();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('If you want to specify a reference with prefix "post-", method "getInexistentField" or "__toString()" must exist in the class, or you can override the "getMethodNameForReference" method and add your own.');

        (new InexistentMethodPrefixFixtureStub())->load($manager);
    }

    public function test custom number of flushes(): void
    {
        $manager = $this->getObjectManager();

        $fixture = new CustomNumberOfFlushesFixtureStub();
        $fixture->load($manager);

        self::assertSame(5, $manager->getFlushed());
        self::assertCount(20, $manager->getPersisted());
    }

    public function test entities with ids(): void
    {
        $entityManager = $this->getEntityManager();

        $fixture = new ObjectsWithIdsStub();
        $fixture->load($entityManager);

        self::assertSame(1, $entityManager->getFlushed());
        self::assertCount(2, $entityManager->getPersisted());
    }

    public function test documents with ids(): void
    {
        $documentManager = $this->getDocumentManager();

        $fixture = new ObjectsWithIdsStub();
        $fixture->load($documentManager);

        self::assertSame(1, $documentManager->getFlushed());
        self::assertCount(2, $documentManager->getPersisted());
    }

    private function getObjectManager(): ObjectManagerStub
    {
        $metadata = $this->createMock(ClassMetadataInterface::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']); // Default to "id" since composite are not supported yet.

        return new ObjectManagerStub($metadata);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $metadata = $this->createMock(ORMClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']); // Default to "id" since composite are not supported yet.

        $driver = $this->createMock(Driver::class);

        $stub = new EntityManagerStub($metadata);
        $stub->setDriver($driver);

        return $stub;
    }

    private function getDocumentManager(): DocumentManager
    {
        $metadata = $this->createMock(ODMClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']); // Default to "id" since composite are not supported yet.

        return new DocumentManagerStub($metadata);
    }
}
