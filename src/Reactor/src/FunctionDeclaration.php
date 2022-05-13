<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Nette\PhpGenerator\Factory;
use Nette\PhpGenerator\GlobalFunction;

class FunctionDeclaration implements AggregableInterface, DeclarationInterface, NamedInterface, \Stringable
{
    use Traits\FunctionLike;
    use Traits\NameAware;
    use Traits\CommentAware;
    use Traits\AttributeAware;

    protected GlobalFunction $element;

    public function __construct(string $name)
    {
        $this->element = new GlobalFunction($name);
    }

    public function __toString(): string
    {
        return $this->element->__toString();
    }

    public static function from(string $function, bool $withBody = false): static
    {
        return static::fromElement(
            (new Factory())->fromFunctionReflection(new \ReflectionFunction($function), $withBody)
        );
    }

    public function render(): string
    {
        return $this->__toString();
    }

    /**
     * @internal
     */
    public static function fromElement(GlobalFunction $element): static
    {
        $function = new static($element->getName());

        $function->element = $element;

        return $function;
    }

    /**
     * @internal
     */
    public function getElement(): GlobalFunction
    {
        return $this->element;
    }
}
