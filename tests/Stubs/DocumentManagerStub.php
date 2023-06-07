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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

class DocumentManagerStub extends DocumentManager
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
        // Override constructor and make it empty for the sake of testing.
    }

    public function getClassMetadata($className): ClassMetadata
    {
        return $this->metadata;
    }

    public function getPersisted(): array
    {
        return $this->persisted;
    }

    public function getFlushed(): int
    {
        return $this->flushed;
    }

    public function persist($document): void
    {
        $this->persisted[] = $document;
    }

    public function flush(array $options = [])
    {
        return $this->flushed++;
    }

    public function clear($documentName = null): void
    {
    }
}
