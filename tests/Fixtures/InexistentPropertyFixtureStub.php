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

namespace Tests\Orbitale\Component\ArrayFixture\Fixtures;

use Orbitale\Component\ArrayFixture\ArrayFixture;
use Tests\Orbitale\Component\ArrayFixture\Stubs\PostStub;

class InexistentPropertyFixtureStub extends ArrayFixture
{
    protected function getEntityClass(): string
    {
        return PostStub::class;
    }

    protected function getObjects(): iterable
    {
        yield [
            'does_not_exist' => 'does_not',
        ];
    }
}
