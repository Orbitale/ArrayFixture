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

/**
 * @extends ArrayFixture<PostStub>
 */
class PostSelfReferenceFixtureStub extends ArrayFixture
{
    protected function getEntityClass(): string
    {
        return PostStub::class;
    }

    protected function getReferencePrefix(): ?string
    {
        return 'posts-';
    }

    protected function getMethodNameForReference(): string
    {
        return 'getTitle';
    }

    protected function getObjects(): iterable
    {
        yield [
            'title' => 'Default title',
            'parent' => null,
        ];
        yield [
            'title' => 'Second title',
            'parent' => function (PostStub $o, self $fixture) {
                return $fixture->getReference('posts-Default title');
            },
        ];
    }
}
