<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Nette\PhpGenerator\Method as NetteMethod;
use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\Traits;
use Spiral\Reactor\Traits\AttributeAware;
use Spiral\Reactor\Traits\CommentAware;
use Spiral\Reactor\Traits\FunctionLike;
use Spiral\Reactor\Traits\NameAware;
use Spiral\Reactor\Traits\VisibilityAware;

final class Method implements NamedInterface, AggregableInterface, \Stringable
{
    use AttributeAware;
    use CommentAware;
    use FunctionLike;
    use NameAware;
    use VisibilityAware;

    private NetteMethod $element;

    public function __construct(string $name)
    {
        $this->element = new NetteMethod($name);
    }

    public function __toString(): string
    {
        return $this->element->__toString();
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

    public function setFinal(bool $state = true): self
    {
        $this->element->setFinal($state);

        return $this;
    }

    public function isFinal(): bool
    {
        return $this->element->isFinal();
    }

    public function setAbstract(bool $state = true): self
    {
        $this->element->setAbstract($state);

        return $this;
    }

    public function isAbstract(): bool
    {
        return $this->element->isAbstract();
    }

    /**
     * @param string $name without $
     */
    public function addPromotedParameter(string $name, mixed $defaultValue = null): PromotedParameter
    {
        $param = PromotedParameter::fromElement($this->element->addPromotedParameter($name));

        if (\func_num_args() > 1) {
            $param->setDefaultValue($defaultValue);
        }

        return $param;
    }

    /**
     * @internal
     */
    public static function fromElement(NetteMethod $element): self
    {
        $method = new self($element->getName());

        $method->element = $element;

        return $method;
    }

    /**
     * @internal
     */
    public function getElement(): NetteMethod
    {
        return $this->element;
    }
}
