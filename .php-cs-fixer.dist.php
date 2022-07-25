<?php

declare(strict_types=1);

if (!file_exists(__DIR__.'/src')) {
    exit(0);
}

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'ternary_operator_spaces' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->files()
            ->name('*.php')
            ->in(__DIR__ . '/src')
            ->append([__FILE__])
            ->notPath(['#/Fixtures/#', '#/tests/#', '#/views/#'])
    )
    ->setCacheFile('.php-cs-fixer.cache');
