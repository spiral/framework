<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\PhpNamespace;

/**
 * TraitUses aggregation. Can automatically create TraitUse on demand.
 *
 * @method $this add(PhpNamespace $element)
 */
final class Namespaces extends Aggregator
{
    public function __construct(array $namespaces)
    {
        parent::__construct([PhpNamespace::class], $namespaces);
    }

    /**
     * Get named element by it's name.
     *
     * @return PhpNamespace|AggregableInterface
     */
    public function get(string $name): PhpNamespace
    {
        if (!$this->has($name)) {
            $namespaces = new PhpNamespace($name);
            $this->add($namespaces);

            return $namespaces;
        }

        return parent::get($name);
    }
}
