<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\PhpNamespace;

/**
 * TraitUses aggregation
 *
 * @method $this add(PhpNamespace $element)
 * @method PhpNamespace|AggregableInterface get(string $name)
 */
final class Namespaces extends Aggregator
{
    public function __construct(array $namespaces)
    {
        parent::__construct([PhpNamespace::class], $namespaces);
    }
}
