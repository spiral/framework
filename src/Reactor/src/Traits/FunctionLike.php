<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Nette\PhpGenerator\Parameter as NetteParameter;
use Spiral\Reactor\Aggregator\Parameters;
use Spiral\Reactor\Partial\Parameter;

/**
 * @internal
 */
trait FunctionLike
{
    public function setBody(string $code, ?array $args = null): static
    {
        $this->element->setBody($code, $args);

        return $this;
    }

    public function getBody(): string
    {
        return $this->element->getBody();
    }

    public function addBody(string $code, ?array $args = null): static
    {
        $this->element->addBody($code, $args);

        return $this;
    }

    public function setParameters(Parameters $parameters): static
    {
        $this->element->setParameters(\array_map(
            static fn (Parameter $parameter) => $parameter->getElement(),
            \iterator_to_array($parameters)
        ));

        return $this;
    }

    public function getParameters(): Parameters
    {
        return new Parameters(\array_map(
            static fn (NetteParameter $parameter) => Parameter::fromElement($parameter),
            $this->element->getParameters()
        ));
    }

    /**
     * @param string $name without $
     */
    public function addParameter(string $name, mixed $defaultValue = null): Parameter
    {
        $param = Parameter::fromElement($this->element->addParameter($name));

        if (\func_num_args() > 1) {
            $param->setDefaultValue($defaultValue);
        }

        return $param;
    }

    /**
     * @param string $name without $
     */
    public function removeParameter(string $name): static
    {
        $this->element->removeParameter($name);

        return $this;
    }

    public function setVariadic(bool $state = true): static
    {
        $this->element->setVariadic($state);

        return $this;
    }

    public function isVariadic(): bool
    {
        return $this->element->isVariadic();
    }

    public function setReturnType(?string $type): static
    {
        $this->element->setReturnType($type);

        return $this;
    }

    public function getReturnType(): ?string
    {
        $type = $this->element->getReturnType(false);

        return $type === null ? null : (string) $type;
    }

    public function setReturnReference(bool $state = true): static
    {
        $this->element->setReturnReference($state);

        return $this;
    }

    public function getReturnReference(): bool
    {
        return $this->element->getReturnReference();
    }

    public function setReturnNullable(bool $state = true): static
    {
        $this->element->setReturnNullable($state);

        return $this;
    }

    public function isReturnNullable(): bool
    {
        return $this->element->isReturnNullable();
    }
}
