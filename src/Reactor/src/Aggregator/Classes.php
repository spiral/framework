<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\ClassDeclaration;

/**
 * Classes aggregation.
 *
 * @method $this add(ClassDeclaration $element)
 * @method ClassDeclaration|AggregableInterface get(string $name)
 */
final class Classes extends Aggregator
{
    public function __construct(array $classes)
    {
        parent::__construct([ClassDeclaration::class], $classes);
    }
}
