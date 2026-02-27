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

use Orbitale\Component\ArrayFixture\Ref;
use Orbitale\Component\ArrayFixture\StaticFixture;
use Tests\Orbitale\Component\ArrayFixture\Stubs\PostStub;

/**
 * @extends StaticFixture<PostStub>
 */
class StaticPostFixtureStub extends StaticFixture
{
    protected function getEntityClass(): string
    {
        return PostStub::class;
    }

    public static function getStaticData(): iterable
    {
        return [
            'post-1' => [
                'name' => 'First post',
                'title' => 'First title',
                'parent' => null,
                'tags' => ['php', 'doctrine'],
            ],
            'post-2' => [
                'name' => 'Second post',
                'title' => 'Second title',
                'parent' => new Ref(PostStub::class, 'parent-ref'),
                'tags' => ['symfony'],
            ],
            'post-3' => [
                'name' => 'Third post',
                'title' => 'First title',
                'parent' => null,
                'tags' => [],
            ],
        ];
    }
}
