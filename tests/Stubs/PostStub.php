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

class PostStub
{
    /** @var string */
    protected $title;

    /** @var array<string> */
    private $tags = [];

    /** @var null|PostStub */
    private $parent;

    public function getTitle(): string
    {
        return $this->title;
    }

    /** @return array<string> */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }
}
