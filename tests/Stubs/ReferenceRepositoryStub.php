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
    private array $references = [];

    public function addReference($name, $object): void
    {
        $this->references[$name] = $object;
    }

    public function hasReference($name)
    {
        return isset($this->references[$name]);
    }

    public function getReference($name)
    {
        if (!$this->hasReference($name)) {
            throw new OutOfBoundsException(\sprintf('Reference to "%s" does not exist', $name));
        }

        return $this->references[$name];
    }

    public function getReferences()
    {
        return $this->references;
    }
}
