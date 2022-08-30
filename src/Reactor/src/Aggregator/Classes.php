<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\ClassDeclaration;

/**
 * Classes aggregation.
 *
 * @extends Aggregator<ClassDeclaration>
 */
final class Classes extends Aggregator
{
    public function __construct(array $classes)
    {
        parent::__construct([ClassDeclaration::class], $classes);
    }
}
