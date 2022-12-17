<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\InterfaceType;
use Spiral\Reactor\Aggregator\Interfaces;
use Spiral\Reactor\InterfaceDeclaration;

/**
 * @internal
 */
trait InterfaceAware
{
    public function addInterface(string $name): InterfaceDeclaration
    {
        return InterfaceDeclaration::fromElement($this->element->addInterface($name));
    }

    public function getInterface(string $name): InterfaceDeclaration
    {
        /**
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        return $this->getInterfaces()->get(Helpers::extractShortName($name));
    }

    public function getInterfaces(): Interfaces
    {
        $interfaces = \array_filter(
            $this->element->getClasses(),
            static fn (ClassLike $element): bool => $element instanceof InterfaceType
        );

        return new Interfaces(\array_map(
            static fn (InterfaceType $interface): InterfaceDeclaration => InterfaceDeclaration::fromElement($interface),
            $interfaces
        ));
    }
}
