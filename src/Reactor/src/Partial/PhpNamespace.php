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
use Spiral\Reactor\Aggregator\Elements;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\EnumDeclaration;
use Spiral\Reactor\InterfaceDeclaration;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\TraitDeclaration;
use Spiral\Reactor\Traits;

final class PhpNamespace implements NamedInterface, AggregableInterface, \Stringable
{
    use Traits\ClassAware;
    use Traits\EnumAware;
    use Traits\InterfaceAware;
    use Traits\NameAware;
    use Traits\TraitAware;

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
     * @deprecated since v3.5. Will be removed in v4.0
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
