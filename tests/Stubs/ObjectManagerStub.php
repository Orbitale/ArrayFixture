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

if (PHP_VERSION_ID < 80100) {
    require_once __DIR__.'/ObjectManagerStub_php80.php';
} else {
    require_once __DIR__.'/ObjectManagerStub_php81.php';
}
