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
use Tests\Orbitale\Component\ArrayFixture\Stubs\StringablePostStub;

class CustomNumberOfFlushesFixtureStub extends ArrayFixture
{
    protected function getEntityClass(): string
    {
        return PostStub::class;
    }

    protected function flushEveryXIterations(): int
    {
        return 5;
    }

    protected function getObjects(): iterable
    {
        for ($i = 0; $i < 20; $i++) {
            yield [
                'title' => 'Title'.$i,
            ];
        }
    }
}
