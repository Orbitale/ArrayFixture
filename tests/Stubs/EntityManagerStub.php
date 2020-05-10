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

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;

class EntityManagerStub implements ObjectManager
{
    private ClassMetadata $metadata;
    private array $persisted = [];
    private int $flushed = 0;

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

    public function flush()
    {
        return $this->flushed++;
    }

    public function getClassMetadata($className)
    {
        return $this->metadata;
    }

    public function find($className, $id): void
    {
    }

    public function remove($object): void
    {
    }

    public function merge($object): void
    {
    }

    public function clear($objectName = null): void
    {
    }

    public function detach($object): void
    {
    }

    public function refresh($object): void
    {
    }

    public function getRepository($className): void
    {
    }

    public function getMetadataFactory(): void
    {
    }

    public function initializeObject($obj): void
    {
    }

    public function contains($object): void
    {
    }
}
