<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\TraitUse;

/**
 * TraitUses aggregation
 *
 * @extends Aggregator<TraitUse>
 */
final class TraitUses extends Aggregator
{
    public function __construct(array $traitUses)
    {
        parent::__construct([TraitUse::class], $traitUses);
    }
}
