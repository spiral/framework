<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

return \Spiral\CodeStyle\Builder::create()
    ->include(__DIR__ . '/builder')
    ->include(__DIR__ . '/src')
    ->include(__DIR__ . '/tests')
    ->include(__FILE__)
    ->exclude(__DIR__ . '/src/Core/tests/Fixtures')
    ->exclude(__DIR__ . '/src/Tokenizer/tests')
    ->exclude(__DIR__ . '/src/Prototype/tests')
    ->exclude(__DIR__ . '/src/Snapshots/tests')
    ->exclude(__DIR__ . '/src/Core/tests/Internal/Proxy/ProxyClassRendererTest.php')
    ->exclude(__DIR__ . '/src/Core/tests/Exception/ClosureRendererTraitTest.php')
    ->cache('./runtime/php-cs-fixer.cache')
    ->allowRisky(true)
    ->build();
