<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\TraitUse;

/**
 * TraitUses aggregation
 *
 * @method $this add(TraitUse $element)
 * @method TraitUse|AggregableInterface get(string $name)
 */
final class TraitUses extends Aggregator
{
    public function __construct(array $traitUses)
    {
        parent::__construct([TraitUse::class], $traitUses);
    }
}
