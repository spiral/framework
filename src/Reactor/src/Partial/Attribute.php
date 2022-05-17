<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Nette\PhpGenerator\Attribute as NetteAttribute;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\Traits\NameAware;

final class Attribute implements NamedInterface
{
    use NameAware;

    private NetteAttribute $element;

    public function __construct(string $name, array $args)
    {
        $this->element = new NetteAttribute($name, $args);
    }

    /** @return mixed[] */
    public function getArguments(): mixed
    {
        return $this->element->getArguments();
    }

    /**
     * @internal
     */
    public static function fromElement(NetteAttribute $element): self
    {
        $attribute = new self($element->getName(), $element->getArguments());

        $attribute->element = $element;

        return $attribute;
    }

    /**
     * @internal
     */
    public function getElement(): NetteAttribute
    {
        return $this->element;
    }
}
