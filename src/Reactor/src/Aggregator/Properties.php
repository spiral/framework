<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\Property;

/**
 * Property aggregation
 *
 * @extends Aggregator<Property>
 */
final class Properties extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Property::class], $constants);
    }
}
