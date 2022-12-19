<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\TraitType;
use Spiral\Reactor\Aggregator\Traits;
use Spiral\Reactor\TraitDeclaration;

/**
 * @internal
 */
trait TraitAware
{
    public function addTrait(string $name): TraitDeclaration
    {
        return TraitDeclaration::fromElement($this->element->addTrait($name));
    }

    public function getTrait(string $name): TraitDeclaration
    {
        /**
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        return $this->getTraits()->get(Helpers::extractShortName($name));
    }

    public function getTraits(): Traits
    {
        $traits = \array_filter(
            $this->element->getClasses(),
            static fn (ClassLike $element): bool => $element instanceof TraitType
        );

        return new Traits(\array_map(
            static fn (TraitType $trait): TraitDeclaration => TraitDeclaration::fromElement($trait),
            $traits
        ));
    }
}
