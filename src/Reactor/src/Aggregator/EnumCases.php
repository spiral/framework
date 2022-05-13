<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\EnumCase;

/**
 * TraitUses aggregation. Can automatically create TraitUse on demand.
 *
 * @method $this add(EnumCase $element)
 */
final class EnumCases extends Aggregator
{
    public function __construct(array $enumCases)
    {
        parent::__construct([EnumCase::class], $enumCases);
    }

    /**
     * Get named element by it's name.
     *
     * @return EnumCase|AggregableInterface
     */
    public function get(string $name): EnumCase
    {
        if (!$this->has($name)) {
            $enumCases = new EnumCase($name);
            $this->add($enumCases);

            return $enumCases;
        }

        return parent::get($name);
    }
}
