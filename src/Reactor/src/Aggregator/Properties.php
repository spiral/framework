<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\Property;

/**
 * Property aggregation
 *
 * @method $this add(Property $element)
 * @method Property|AggregableInterface get(string $name)
 */
final class Properties extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Property::class], $constants);
    }
}
