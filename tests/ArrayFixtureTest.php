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

use Doctrine\Persistence\Mapping\ClassMetadata;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\CustomNumberOfFlushesFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\InexistentMethodPrefixFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\InexistentPropertyFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\PostSelfReferenceFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\PostTitleFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Fixtures\ToStringPrefixFixtureStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\EntityManagerStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\PostStub;
use Tests\Orbitale\Component\ArrayFixture\Stubs\ReferenceRepositoryStub;

class ArrayFixtureTest extends TestCase
{
    public function test post title fixture(): void
    {
        $manager = $this->getEntityManager();

        (new PostTitleFixtureStub())->load($manager);

        static::assertSame(1, $manager->getFlushed());

        $entities = $manager->getPersisted();
        static::assertCount(1, $entities);
        static::assertInstanceOf(PostStub::class, $entities[0]);
        static::assertSame('Default title', $entities[0]->getTitle());
    }

    public function test post self reference fixture(): void
    {
        $manager = $this->getEntityManager();
        $refs = new ReferenceRepositoryStub($manager);

        $fixture = new PostSelfReferenceFixtureStub();
        $fixture->setReferenceRepository($refs);
        $fixture->load($manager);

        static::assertSame(1, $manager->getFlushed());

        $entities = $manager->getPersisted();
        static::assertCount(2, $entities);
        static::assertInstanceOf(PostStub::class, $entities[0]);
        static::assertInstanceOf(PostStub::class, $entities[1]);
        static::assertSame('Default title', $entities[0]->getTitle());
        static::assertSame('Second title', $entities[1]->getTitle());
        static::assertSame($entities[0], $entities[1]->getParent());
    }

    public function test inexistent property(): void
    {
        $manager = $this->getEntityManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot set property "does_not_exist" to "Tests\Orbitale\Component\ArrayFixture\Stubs\PostStub" object since this property does not exist.');

        (new InexistentPropertyFixtureStub())->load($manager);
    }

    public function test toString for prefix(): void
    {
        $manager = $this->getEntityManager();
        $refsRepo = new ReferenceRepositoryStub($manager);

        $fixture = new ToStringPrefixFixtureStub();
        $fixture->setReferenceRepository($refsRepo);
        $fixture->load($manager);

        static::assertSame(1, $manager->getFlushed());

        $entities = $manager->getPersisted();
        static::assertCount(1, $entities);
        static::assertInstanceOf(PostStub::class, $entities[0]);
        static::assertSame('Default title', $entities[0]->getTitle());
        $refs = $refsRepo->getReferences();
        static::assertArrayHasKey('post-Default title', $refs);
        static::assertSame($refs['post-Default title'], $entities[0]);
    }

    public function test inexistent prefix method(): void
    {
        $manager = $this->getEntityManager();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('If you want to specify a reference with prefix "post-", method "getInexistentField" or "__toString()" must exist in the class, or you can override the "getMethodNameForReference" method and add your own.');

        (new InexistentMethodPrefixFixtureStub())->load($manager);
    }

    public function test custom number of flushes(): void
    {
        $manager = $this->getEntityManager();

        $fixture = new CustomNumberOfFlushesFixtureStub();
        $fixture->load($manager);

        static::assertSame(5, $manager->getFlushed());
        static::assertCount(20, $manager->getPersisted());
    }

    private function getEntityManager(): EntityManagerStub
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturn(['id']); // Default to "id" since composite are not supported yet.

        return new EntityManagerStub($metadata);
    }
}
