<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\Method;

/**
 * Method aggregation
 *
 * @method Method add(Method $element)
 * @method Method|AggregableInterface get(string $name)
 */
final class Methods extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Method::class], $constants);
    }
}
