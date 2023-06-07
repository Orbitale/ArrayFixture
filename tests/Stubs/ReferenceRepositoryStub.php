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

use Doctrine\Common\DataFixtures\ReferenceRepository;
use OutOfBoundsException;

class ReferenceRepositoryStub extends ReferenceRepository
{
    /** @var array<object> */
    private $references = [];

    /** @var array<string, array<string, object>> */
    private $referencesByClass = [];

    public function addReference($name, $object): void
    {
        $this->references[$name] = $object;
        $this->referencesByClass[\get_class($object)][$name] = $object;
    }

    public function hasReference($name, ?string $class = null): bool
    {
        return $class === null
            ? isset($this->references[$name]) // For BC, to be removed in next major.
            : isset($this->referencesByClass[$class][$name]);
    }

    public function getReference($name, ?string $class = null): object
    {
        if (!$this->hasReference($name, $class)) {
            throw new OutOfBoundsException(\sprintf('Reference to "%s" does not exist', $name));
        }

        return $class === null
            ? $this->references[$name] // For BC, to be removed in next major.
            : $this->referencesByClass[$class][$name];
    }

    /** @return array<object> */
    public function getReferences(): array
    {
        return $this->references;
    }
}
