<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\EnumCase;

/**
 * TraitUses aggregation.
 *
 * @extends Aggregator<EnumCase>
 */
final class EnumCases extends Aggregator
{
    public function __construct(array $enumCases)
    {
        parent::__construct([EnumCase::class], $enumCases);
    }
}
