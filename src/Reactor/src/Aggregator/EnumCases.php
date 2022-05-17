<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\EnumCase;

/**
 * TraitUses aggregation.
 *
 * @method $this add(EnumCase $element)
 * @method EnumCase|AggregableInterface get(string $name)
 */
final class EnumCases extends Aggregator
{
    public function __construct(array $enumCases)
    {
        parent::__construct([EnumCase::class], $enumCases);
    }
}
