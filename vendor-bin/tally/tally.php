<?php

declare(strict_types=1);

use Tally\Attribute\RelativeTolerance;
use Tally\Configuration;

// Tally loads only its own autoloader from vendor-bin; the benchmarks need the project's.
require_once __DIR__ . '/../../vendor/autoload.php';

return Configuration::create()
    ->withPaths([__DIR__ . '/../../tests/Bench'])
    ->withIniEntries(['memory_limit=-1'])
    // The default ±10 % lets through a quadratic term that is still small next to the linear cost
    // at the sizes the limits of nested filters allow.
    ->withGrowthTolerance(new RelativeTolerance(0.05));
