<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\Parameter;

/**
 * Constants aggregation
 *
 * @method $this add(Parameter $element)
 * @method Parameter|AggregableInterface get(string $name)
 */
final class Parameters extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Parameter::class], $constants);
    }
}
