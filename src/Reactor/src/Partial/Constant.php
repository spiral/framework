<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Nette\PhpGenerator\Constant as NetteConstant;
use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\Traits;

final class Constant implements NamedInterface, AggregableInterface
{
    use Traits\AttributeAware;
    use Traits\CommentAware;
    use Traits\NameAware;
    use Traits\VisibilityAware;

    private NetteConstant $element;

    public function __construct(string $name, mixed $value = null)
    {
        $this->element = new NetteConstant(
            \strtoupper((new InflectorFactory())->build()->tableize(\strtolower($name))),
        );
        $this->element->setValue($value);
    }

    /**
     * @internal
     */
    public static function fromElement(NetteConstant $element): self
    {
        $constant = new self($element->getName());

        $constant->element = $element;

        return $constant;
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

    public function setFinal(bool $state = true): self
    {
        $this->element->setFinal($state);

        return $this;
    }

    public function isFinal(): bool
    {
        return $this->element->isFinal();
    }

    public function setType(?string $type): self
    {
        $this->element->setType($type);

        return $this;
    }

    public function getType(): ?string
    {
        return $this->element->getType();
    }

    /**
     * @internal
     */
    public function getElement(): NetteConstant
    {
        return $this->element;
    }
}
