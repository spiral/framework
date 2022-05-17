<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Nette\PhpGenerator\Attribute as NetteAttribute;
use Spiral\Reactor\Partial\Attribute;

/**
 * @internal
 */
trait AttributeAware
{
    /** @param mixed[] $args */
    public function addAttribute(string $name, array $args = []): static
    {
        $this->element->addAttribute($name, $args);

        return $this;
    }

    /**
     * @param Attribute[] $attributes
     */
    public function setAttributes(array $attributes): static
    {
        $this->element->setAttributes(\array_map(
            static fn (Attribute $attribute) => $attribute->getElement(),
            $attributes
        ));

        return $this;
    }

    /** @return Attribute[] */
    public function getAttributes(): array
    {
        return \array_map(
            static fn (NetteAttribute $attribute) => Attribute::fromElement($attribute),
            $this->element->getAttributes()
        );
    }
}
