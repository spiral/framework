<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\Constant;

/**
 * Constants aggregation.
 *
 * @method $this add(Constant $element)
 * @method Constant|AggregableInterface get(string $name)
 */
final class Constants extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Constant::class], $constants);
    }
}
