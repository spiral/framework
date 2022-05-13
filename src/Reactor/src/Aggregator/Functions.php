<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\FunctionDeclaration;

/**
 * TraitUses aggregation
 *
 * @method $this add(FunctionDeclaration $element)
 * @method FunctionDeclaration|AggregableInterface get(string $name)
 */
final class Functions extends Aggregator
{
    public function __construct(array $functions)
    {
        parent::__construct([FunctionDeclaration::class], $functions);
    }
}
