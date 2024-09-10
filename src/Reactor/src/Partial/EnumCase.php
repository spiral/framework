<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Nette\PhpGenerator\EnumCase as NetteEnumCase;
use Nette\PhpGenerator\Literal;
use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\Traits;
use Spiral\Reactor\Traits\AttributeAware;
use Spiral\Reactor\Traits\CommentAware;
use Spiral\Reactor\Traits\NameAware;

final class EnumCase implements NamedInterface, AggregableInterface
{
    use AttributeAware;
    use CommentAware;
    use NameAware;

    private NetteEnumCase $element;

    public function __construct(string $name)
    {
        $this->element = new NetteEnumCase($name);
    }

    public function setValue(string|int|null $value): self
    {
        $this->element->setValue($value);

        return $this;
    }

    public function getValue(): string|int|null|Literal
    {
        return $this->element->getValue();
    }

    /**
     * @internal
     */
    public static function fromElement(NetteEnumCase $element): self
    {
        $enumCase = new self($element->getName());

        $enumCase->element = $element;

        return $enumCase;
    }

    /**
     * @internal
     */
    public function getElement(): NetteEnumCase
    {
        return $this->element;
    }
}
