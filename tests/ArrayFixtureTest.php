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
use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\CustomNumberOfFlushesFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\InexistentMethodPrefixFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\InexistentPropertyFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\ObjectsWithIdsStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\PostSelfReferenceFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\PostTitleFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\ToStringPrefixFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\PostStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\ReferenceRepositoryStub;

class ArrayFixtureTest extends TestCase
{
    public function test_post_title_fixture(): void
    {
        $manager = $this->getObjectManager();

        $manager->expects($this->once())->method('flush');
        $manager->expects($this->once())->method('persist')->with(
            new Callback(function ($stub) {
                return is_object($stub) && $stub instanceof PostStub && $stub->getTitle() === 'Default title';
            }),
        );

        //

        (new PostTitleFixtureStub())->load($manager);
    }

    public function test_post_self_reference_fixture(): void
    {
        $manager = $this->getObjectManager();

        $titles = [
            'Default title',
            'Second title',
        ];
        /** @var null|PostStub $lastStub */
        $lastStub = null;

        $manager->expects($this->once())->method('flush');
        $manager->expects($this->exactly(2))->method('persist')->with(
            new Callback(function ($stub) use (&$titles, &$lastStub) {
                if (!count($titles)) {
                    self::fail('Persist was called too many times');
                }
                $title = array_shift($titles);
                $result = is_object($stub)
                    && $stub instanceof PostStub
                    && $stub->getTitle() === $title
                    && ($lastStub ? $stub->getParent() === $lastStub : true)
                ;
                $lastStub = $stub;
                return $result;
            }),
        );

        //

        $fixture = new PostSelfReferenceFixtureStub();
        $fixture->setReferenceRepository(new ReferenceRepositoryStub($manager));
        $fixture->load($manager);
    }

    public function test_inexistent_property(): void
    {
        $manager = $this->getObjectManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot set property "does_not_exist" to "Tests\Orbitale\Component\ArrayFixture\Stubs\PostStub" object since this property does not exist.');

        //

        (new InexistentPropertyFixtureStub())->load($manager);
    }

    public function test_toString_for_prefix(): void
    {
        $manager = $this->getObjectManager();
        $refsRepo = new ReferenceRepositoryStub($manager);

        /** @var PostStub $persistedStub */
        $persistedStub = null;

        $manager->expects($this->once())->method('flush');
        $manager->expects($this->once())->method('persist')->with(
            new Callback(function ($stub) use (&$persistedStub) {
                $result = is_object($stub) && $stub instanceof PostStub && $stub->getTitle() === 'Default title';
                if ($result) {
                    $persistedStub = $stub;
                }
                return $result;
            }),
        );

        //

        $fixture = new ToStringPrefixFixtureStub();
        $fixture->setReferenceRepository($refsRepo);
        $fixture->load($manager);

        //

        $refs = $refsRepo->getReferences();
        self::assertArrayHasKey('post-Default title', $refs);
        self::assertSame($refs['post-Default title'], $persistedStub);
    }

    public function test_inexistent_prefix_method(): void
    {
        $manager = $this->getObjectManager();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('If you want to specify a reference with prefix "post-", method "getInexistentField" or "__toString()" must exist in the class, or you can override the "getMethodNameForReference" method and add your own.');

        //

        (new InexistentMethodPrefixFixtureStub())->load($manager);
    }

    public function test_custom_number_of_flushes(): void
    {
        $manager = $this->getObjectManager();

        $manager->expects($this->exactly(5))->method('flush');
        $manager->expects($this->exactly(20))->method('persist');

        //

        $manager->expects($this->exactly(5))->method('flush');
        $manager->expects($this->exactly(20))->method('persist')->with(
            new Callback(function ($stub) use (&$persistedStubs) {
                $result = is_object($stub) && $stub instanceof PostStub;
                if ($result) {
                    $persistedStubs[] = $stub;
                }
                return $result;
            }),
        );

        //

        (new CustomNumberOfFlushesFixtureStub())->load($manager);
        self::assertCount(20, $persistedStubs);
        for ($i = 0; $i < 20; $i++) {
            self::assertSame('Title'.$i, $persistedStubs[$i]->getTitle());
            self::assertNull($persistedStubs[$i]->getParent());
        }
    }

    public function test_entities_with_ids(): void
    {
        $entityManager = $this->getEntityManager();

        /** @var PostStub[] $persistedStubs */
        $persistedStubs = [];

        $entityManager->expects($this->once())->method('flush')->withAnyParameters();
        $entityManager->expects($this->exactly(2))->method('persist')->with(
            new Callback(function ($stub) use (&$persistedStubs) {
                $result = is_object($stub) && $stub instanceof PostStub;
                if ($result) {
                    $persistedStubs[] = $stub;
                }
                return $result;
            }),
        );

        //

        (new ObjectsWithIdsStub())->load($entityManager);
        self::assertSame('Default title', $persistedStubs[0]->getTitle());
        self::assertNull($persistedStubs[0]->getParent());
        self::assertSame('Another title', $persistedStubs[1]->getTitle());
        self::assertNull($persistedStubs[1]->getParent());
    }

    public function test_documents_with_ids(): void
    {
        $documentManager = $this->getDocumentManager();

        /** @var PostStub[] $persistedStubs */
        $persistedStubs = [];

        $documentManager->expects($this->once())->method('flush')->withAnyParameters();
        $documentManager->expects($this->exactly(2))->method('persist')->with(
            new Callback(function ($stub) use (&$persistedStubs) {
                $result = is_object($stub) && $stub instanceof PostStub;
                if ($result) {
                    $persistedStubs[] = $stub;
                }
                return $result;
            }),
        );


        //

        (new ObjectsWithIdsStub())->load($documentManager);
        self::assertSame('Default title', $persistedStubs[0]->getTitle());
        self::assertNull($persistedStubs[0]->getParent());
        self::assertSame('Another title', $persistedStubs[1]->getTitle());
        self::assertNull($persistedStubs[1]->getParent());
    }

    /**
     * @return ObjectManager&MockObject
     */
    private function getObjectManager(): ObjectManager
    {
        $metadata = $this->createMock(ORMClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']); // Default to "id" since composite are not supported yet.

        $om = $this->createMock(ObjectManager::class);
        $om->method('getClassMetadata')->willReturn($metadata);

        return $om;
    }

    /**
     * @return EntityManagerInterface&MockObject
     */
    private function getEntityManager(): EntityManagerInterface
    {
        $metadata = $this->createMock(ORMClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']); // Default to "id" since composite are not supported yet.

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getClassMetadata')->willReturn($metadata);

        return $em;
    }

    /**
     * @return DocumentManager&MockObject
     */
    private function getDocumentManager(): DocumentManager
    {
        $metadata = $this->createMock(ODMClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']); // Default to "id" since composite are not supported yet.

        $dm = $this->createStub(DocumentManager::class);
        $dm->method('getClassMetadata')->willReturn($metadata);

        return $dm;
    }
}
