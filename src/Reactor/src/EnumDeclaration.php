<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Nette\PhpGenerator\EnumCase as NetteEnumCase;
use Nette\PhpGenerator\EnumType;
use Spiral\Reactor\Aggregator\EnumCases;
use Spiral\Reactor\Partial\Constant;
use Spiral\Reactor\Partial\EnumCase;
use Spiral\Reactor\Partial\Method;
use Spiral\Reactor\Partial\TraitUse;
use Spiral\Reactor\Traits;

/**
 * @extends AbstractDeclaration<EnumType>
 */
class EnumDeclaration extends AbstractDeclaration implements AggregableInterface
{
    use Traits\ConstantsAware;
    use Traits\MethodsAware;
    use Traits\TraitsAware;

    public function __construct(string $name)
    {
        $this->element = new EnumType($name);
    }

    public function setType(?string $type): static
    {
        $this->element->setType($type);

        return $this;
    }

    public function getType(): ?string
    {
        return $this->element->getType();
    }

    /**
     * @param string[] $names
     */
    public function setImplements(array $names): static
    {
        $this->element->setImplements($names);

        return $this;
    }

    /** @return string[] */
    public function getImplements(): array
    {
        return $this->element->getImplements();
    }

    public function addImplement(string $name): static
    {
        $this->element->addImplement($name);

        return $this;
    }

    public function removeImplement(string $name): static
    {
        $this->element->removeImplement($name);

        return $this;
    }

    public function setCases(EnumCases $enumCases): static
    {
        $this->element->setCases(\array_map(
            static fn (EnumCase $enumCase) => $enumCase->getElement(),
            \iterator_to_array($enumCases)
        ));

        return $this;
    }

    public function getCases(): EnumCases
    {
        return new EnumCases(\array_map(
            static fn (NetteEnumCase $enumCase) => EnumCase::fromElement($enumCase),
            $this->element->getCases()
        ));
    }

    public function addCase(string $name, string|int|null $value = null): EnumCase
    {
        return EnumCase::fromElement($this->element->addCase($name, $value));
    }

    public function getCase(string $name): EnumCase
    {
        return $this->getCases()->get($name);
    }

    public function removeCase(string $name): static
    {
        $this->element->removeCase($name);

        return $this;
    }

    public function addMember(Method|Constant|EnumCase|TraitUse $member): static
    {
        $this->element->addMember($member->getElement());

        return $this;
    }

    /**
     * @internal
     */
    public static function fromElement(EnumType $element): static
    {
        $enum = new static($element->getName());

        $enum->element = $element;

        return $enum;
    }

    /**
     * @internal
     */
    public function getElement(): EnumType
    {
        return $this->element;
    }
}
