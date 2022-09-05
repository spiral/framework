<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\FunctionDeclaration;

/**
 * TraitUses aggregation
 *
 * @extends Aggregator<FunctionDeclaration>
 */
final class Functions extends Aggregator
{
    public function __construct(array $functions)
    {
        parent::__construct([FunctionDeclaration::class], $functions);
    }
}
