<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace as NettePhpNamespace;
use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\Aggregator\Classes;
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

    public function removeClass(string $name): self
    {
        $this->element->removeClass($name);

        return $this;
    }

    public function getClasses(): Classes
    {
        return new Classes(\array_map(
            static fn (ClassType $class) => ClassDeclaration::fromElement($class),
            $this->element->getClasses()
        ));
    }

    public function addInterface(string $name): InterfaceDeclaration
    {
        return InterfaceDeclaration::fromElement($this->element->addInterface($name));
    }

    public function addTrait(string $name): TraitDeclaration
    {
        return TraitDeclaration::fromElement($this->element->addTrait($name));
    }

    public function addEnum(string $name): EnumDeclaration
    {
        return EnumDeclaration::fromElement($this->element->addEnum($name));
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
