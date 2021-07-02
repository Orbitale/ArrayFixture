<?php

declare(strict_types=1);

/*
 * This file is part of the Agate Apps package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com> and Studio Agate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$header = <<<'HEADER'
This file is part of the Orbitale ArrayFixture package.

(c) Alexandre Rock Ancelet <alex@orbitale.io>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
HEADER;

$finder = PhpCsFixer\Finder::create()
    ->exclude([
        '.php_cs',
        'public',
        'vendor',
        'var',
    ])
    ->in([
        __DIR__.'/src/',
        __DIR__.'/tests/',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        'header_comment' => [
            'header' => $header,
        ],
        '@DoctrineAnnotation' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHP70Migration' => true,
        '@PHP70Migration:risky' => true,
        '@PHP71Migration' => true,
        '@PHP71Migration:risky' => true,
        '@PHP73Migration' => true,

        // Rules in no set
        'linebreak_after_opening_tag' => true, // More readability.
        'no_php4_constructor' => true, // These are deprecated, so...
        'simplified_null_return' => true, // Removes useless code.
        'global_namespace_import' => true, // Add a "use" statement for everything in the global namespace.
        'native_constant_invocation' => [
            'include' => ['@all'],
            'scope' => 'namespaced',
        ],
        'native_function_invocation' => [
            'include' => ['@all'],
            'scope' => 'namespaced',
        ],

        // Some overrides of existing sets
        'yoda_style' => false,
        'mb_str_functions' => false, // mbstring can be much slower than native str functions.
        'increment_style' => false, // It's quite pointless and reduces readability. Unless executed billions of times in one row.
        'non_printable_character' => false, // Sometimes they're really useful. And it allows writing non-breakable spaces in test names!
        'php_unit_test_class_requires_covers' => false, // From the @PhpCsFixer set, adds "@coversNothing" annots in all tests, so to avoid phpdoc pollution and "proper" coverage, it needs to be disabled.
        'php_unit_internal_class' => false, // From the @PhpCsFixer set, adds "@internal" to all tests. Even though it's a good practice, it's not really useful for most projects.
        'php_unit_dedicate_assert' => ['target' => 'newest'], // Changes assertions like "assertTrue(is_nan($a))" to "assertNan()", etc.
        'php_unit_dedicate_assert_internal_type' => ['target' => 'newest'], // Changes assertions like "assertInternalType("array", ...)" to "assertIsArray()", etc.
    ])
    ->setRiskyAllowed(true)
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setUsingCache(true)
    ->setFinder($finder)
;
