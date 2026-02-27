<?php

namespace Orbitale\Component\ArrayFixture;

final class Ref
{
    public function __construct(
        public string $class,
        public string $name,
    ) {
    }
}
