<?php

declare(strict_types=1);

return PhpCsFixer\Config::create()
    ->setCacheFile(__DIR__ . '/.php_cs.cache')
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'binary_operator_spaces' => [
            'default' => null,
            'operators' => [
                '|' => 'single_space',
                '!==' => 'single_space',
                '!=' => 'single_space',
                '==' => 'single_space',
                '===' => 'single_space',
            ],
        ],
        'ordered_class_elements' => true,
        'trailing_comma_in_multiline_array' => false,
        'declare_strict_types' => true,
        'linebreak_after_opening_tag' => true,
        'blank_line_after_opening_tag' => true,
        'single_quote' => true,
        'lowercase_cast' => true,
        'short_scalar_cast' => true,
        'no_leading_import_slash' => true,
        'declare_equal_normalize' => [
            'space' => 'none',
        ],
        'new_with_braces' => true,
        'no_blank_lines_after_phpdoc' => true,
        'single_blank_line_before_namespace' => true,
        'visibility_required' => ['property', 'method', 'const'],
        'ternary_operator_spaces' => true,
        'unary_operator_spaces' => true,
        'return_type_declaration' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'no_useless_else' => true,
        'no_useless_return' => true,
        'phpdoc_separation' => false,
        'yoda_style' => false,
        'void_return' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude([
                'vendor',
                'bin',
            ])
    );
