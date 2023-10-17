<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Nette\PhpGenerator\Parameter as NetteParameter;
use Nette\PhpGenerator\PromotedParameter as NettePromotedParameter;
use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\Traits;

/**
 * @property NetteParameter $element
 */
class Parameter implements NamedInterface, AggregableInterface
{
    use Traits\AttributeAware;
    use Traits\NameAware;

    protected NettePromotedParameter|NetteParameter $element;

    public function __construct(string $name)
    {
        $this->element = new NetteParameter((new InflectorFactory())->build()->camelize($name));
    }

    public function setReference(bool $state = true): static
    {
        $this->element->setReference($state);

        return $this;
    }

    public function isReference(): bool
    {
        return $this->element->isReference();
    }

    public function setType(?string $type): static
    {
        $this->element->setType($type);

        return $this;
    }

    public function getType(): ?string
    {
        $type = $this->element->getType(false);

        return $type === null ? null : (string) $type;
    }

    public function setNullable(bool $state = true): static
    {
        $this->element->setNullable($state);

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->element->isNullable();
    }

    public function setDefaultValue(mixed $value): static
    {
        $this->element->setDefaultValue($value);

        return $this;
    }

    public function getDefaultValue(): mixed
    {
        return $this->element->getDefaultValue();
    }

    public function hasDefaultValue(): bool
    {
        return $this->element->hasDefaultValue();
    }

    /**
     * @internal
     */
    public static function fromElement(NetteParameter|NettePromotedParameter $element): static
    {
        $parameter = new static($element->getName());

        $parameter->element = $element;

        return $parameter;
    }

    /**
     * @internal
     */
    public function getElement(): NetteParameter
    {
        return $this->element;
    }
}
