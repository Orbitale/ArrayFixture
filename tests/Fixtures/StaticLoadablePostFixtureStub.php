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
class StaticLoadablePostFixtureStub extends StaticFixture
{
    protected function getEntityClass(): string
    {
        return PostStub::class;
    }

    public static function getStaticData(): iterable
    {
        return [
            'post-1' => [
                'title' => 'Basic title',
                'tags' => ['php'],
            ],
            'post-2' => [
                'title' => fn () => 'Closure title',
                'parent' => new Ref(PostStub::class, 'parent-ref'),
                'tags' => [new Ref(PostStub::class, 'tag-ref-1'), new Ref(PostStub::class, 'tag-ref-2')],
            ],
        ];
    }
}
