<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Nette\PhpGenerator\TraitUse as NetteTraitUse;
use Spiral\Reactor\Aggregator\TraitUses;
use Spiral\Reactor\Partial\TraitUse;

/**
 * @internal
 */
trait TraitsAware
{
    public function setTraits(TraitUses $traits): static
    {
        $this->element->setTraits(\array_map(
            static fn (TraitUse $traitUse) => $traitUse->getElement(),
            \iterator_to_array($traits)
        ));

        return $this;
    }

    public function getTraits(): TraitUses
    {
        return new TraitUses(\array_map(
            static fn (NetteTraitUse $trait) => TraitUse::fromElement($trait),
            $this->element->getTraits()
        ));
    }

    public function getTrait(string $name): TraitUse
    {
        return $this->getTraits()->get($name);
    }

    public function addTrait(string $name): TraitUse
    {
        return TraitUse::fromElement($this->element->addTrait($name));
    }

    public function removeTrait(string $name): static
    {
        $this->element->removeTrait($name);

        return $this;
    }
}
