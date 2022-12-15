<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\TraitDeclaration;

/**
 * Traits aggregation.
 *
 * @extends Aggregator<TraitDeclaration>
 */
final class Traits extends Aggregator
{
    public function __construct(array $traits)
    {
        parent::__construct([TraitDeclaration::class], $traits);
    }
}
