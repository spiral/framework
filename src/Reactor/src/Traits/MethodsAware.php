<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Nette\PhpGenerator\Method as NetteMethod;
use Spiral\Reactor\Aggregator\Methods;
use Spiral\Reactor\Partial\Method;

/**
 * @internal
 */
trait MethodsAware
{
    public function setMethods(Methods $methods): static
    {
        $this->element->setMethods(\array_map(
            static fn (Method $method) => $method->getElement(),
            \iterator_to_array($methods)
        ));

        return $this;
    }

    public function getMethods(): Methods
    {
        return new Methods(\array_map(
            static fn (NetteMethod $method) => Method::fromElement($method),
            $this->element->getMethods()
        ));
    }

    public function getMethod(string $name): Method
    {
        return $this->getMethods()->get($name);
    }

    public function addMethod(string $name): Method
    {
        return Method::fromElement($this->element->addMethod($name));
    }

    public function removeMethod(string $name): static
    {
        $this->element->removeMethod($name);

        return $this;
    }

    public function hasMethod(string $name): bool
    {
        return $this->element->hasMethod($name);
    }
}
