<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\TraitUse;

/**
 * TraitUses aggregation. Can automatically create TraitUse on demand.
 *
 * @method $this add(TraitUse $element)
 */
final class TraitUses extends Aggregator
{
    public function __construct(array $traitUses)
    {
        parent::__construct([TraitUse::class], $traitUses);
    }

    /**
     * Get named element by it's name.
     *
     * @return TraitUse|AggregableInterface
     */
    public function get(string $name): TraitUse
    {
        if (!$this->has($name)) {
            $traits = new TraitUse($name);
            $this->add($traits);

            return $traits;
        }

        return parent::get($name);
    }
}
