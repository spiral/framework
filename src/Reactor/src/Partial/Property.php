<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Nette\PhpGenerator\Property as NetteProperty;
use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\Traits;

final class Property implements NamedInterface, AggregableInterface
{
    use Traits\AttributeAware;
    use Traits\CommentAware;
    use Traits\NameAware;
    use Traits\VisibilityAware;

    private NetteProperty $element;

    public function __construct(string $name)
    {
        $this->element = new NetteProperty($name);
    }

    public function setValue(mixed $value): self
    {
        $this->element->setValue($value);

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->element->getValue();
    }

    public function setStatic(bool $state = true): self
    {
        $this->element->setStatic($state);

        return $this;
    }

    public function isStatic(): bool
    {
        return $this->element->isStatic();
    }

    public function setType(?string $type): self
    {
        $this->element->setType($type);

        return $this;
    }

    public function getType(): ?string
    {
        $type = $this->element->getType();

        return $type === null ? null : (string) $type;
    }

    public function setNullable(bool $state = true): self
    {
        $this->element->setNullable($state);

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->element->isNullable();
    }

    public function setInitialized(bool $state = true): self
    {
        $this->element->setInitialized($state);

        return $this;
    }

    public function isInitialized(): bool
    {
        return $this->element->isInitialized();
    }

    public function setReadOnly(bool $state = true): self
    {
        $this->element->setReadOnly($state);

        return $this;
    }

    public function isReadOnly(): bool
    {
        return $this->element->isReadOnly();
    }

    /**
     * @internal
     */
    public static function fromElement(NetteProperty $element): self
    {
        $property = new self($element->getName());

        $property->element = $element;

        return $property;
    }

    /**
     * @internal
     */
    public function getElement(): NetteProperty
    {
        return $this->element;
    }
}
