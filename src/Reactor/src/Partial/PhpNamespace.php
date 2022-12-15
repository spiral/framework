<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\PhpNamespace as NettePhpNamespace;
use Nette\PhpGenerator\TraitType;
use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator\Classes;
use Spiral\Reactor\Aggregator\Elements;
use Spiral\Reactor\Aggregator\Enums;
use Spiral\Reactor\Aggregator\Interfaces;
use Spiral\Reactor\Aggregator\Traits;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\EnumDeclaration;
use Spiral\Reactor\InterfaceDeclaration;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\TraitDeclaration;
use Spiral\Reactor\Traits\NameAware;

final class PhpNamespace implements NamedInterface, AggregableInterface, \Stringable
{
    use NameAware;

    private NettePhpNamespace $element;

    public function __construct(string $name)
    {
        $this->element = new NettePhpNamespace($name);
    }

    public function __toString(): string
    {
        return $this->element->__toString();
    }

    public function hasBracketedSyntax(): bool
    {
        return $this->element->hasBracketedSyntax();
    }

    public function addUse(string $name, ?string $alias = null, string $of = NettePhpNamespace::NameNormal): self
    {
        $this->element->addUse($name, $alias, $of);

        return $this;
    }

    public function removeUse(string $name, string $of = NettePhpNamespace::NameNormal): void
    {
        $this->element->removeUse($name, $of);
    }

    public function addUseFunction(string $name, ?string $alias = null): self
    {
        $this->element->addUseFunction($name, $alias);

        return $this;
    }

    public function addUseConstant(string $name, ?string $alias = null): self
    {
        $this->element->addUseConstant($name, $alias);

        return $this;
    }

    /** @return string[] */
    public function getUses(string $of = NettePhpNamespace::NameNormal): array
    {
        return $this->element->getUses($of);
    }

    public function resolveName(string $name, string $of = NettePhpNamespace::NameNormal): string
    {
        return $this->element->resolveName($name, $of);
    }

    public function simplifyType(string $type, string $of = NettePhpNamespace::NameNormal): string
    {
        return $this->element->simplifyType($type, $of);
    }

    public function simplifyName(string $name, string $of = NettePhpNamespace::NameNormal): string
    {
        return $this->element->simplifyName($name, $of);
    }

    public function addClass(string $name): ClassDeclaration
    {
        return ClassDeclaration::fromElement($this->element->addClass($name));
    }

    public function getClasses(): Classes
    {
        $classes = \array_filter(
            $this->element->getClasses(),
            static fn (ClassLike $element): bool => $element instanceof ClassType
        );

        return new Classes(\array_map(
            static fn (ClassType $class): ClassDeclaration => ClassDeclaration::fromElement($class),
            $classes
        ));
    }

    public function addInterface(string $name): InterfaceDeclaration
    {
        return InterfaceDeclaration::fromElement($this->element->addInterface($name));
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

    public function addTrait(string $name): TraitDeclaration
    {
        return TraitDeclaration::fromElement($this->element->addTrait($name));
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

    public function addEnum(string $name): EnumDeclaration
    {
        return EnumDeclaration::fromElement($this->element->addEnum($name));
    }

    public function getEnums(): Enums
    {
        $enums = \array_filter(
            $this->element->getClasses(),
            static fn (ClassLike $element): bool => $element instanceof EnumType
        );

        return new Enums(\array_map(
            static fn (EnumType $enum): EnumDeclaration => EnumDeclaration::fromElement($enum),
            $enums
        ));
    }

    public function getElements(): Elements
    {
        return new Elements(\array_map(
            static fn (ClassLike $element) => match (true) {
                $element instanceof ClassType => ClassDeclaration::fromElement($element),
                $element instanceof InterfaceType => InterfaceDeclaration::fromElement($element),
                $element instanceof TraitType => TraitDeclaration::fromElement($element),
                $element instanceof EnumType => EnumDeclaration::fromElement($element)
            },
            $this->element->getClasses()
        ));
    }

    /**
     * @deprecated since v3.5.
     * @see PhpNamespace::removeElement
     */
    public function removeClass(string $name): self
    {
        return $this->removeElement($name);
    }

    public function removeElement(string $name): self
    {
        $this->element->removeClass($name);

        return $this;
    }

    /**
     * @internal
     */
    public static function fromElement(NettePhpNamespace $element): self
    {
        $namespace = new self($element->getName());

        $namespace->element = $element;

        return $namespace;
    }

    /**
     * @internal
     */
    public function getElement(): NettePhpNamespace
    {
        return $this->element;
    }
}
