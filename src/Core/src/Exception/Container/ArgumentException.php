<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Container;

/**
 * Unable to resolve argument value.
 */
class ArgumentException extends AutowireException
{
    /**
     * @param \ReflectionParameter $parameter Parameter caused error.
     * @param \ReflectionFunctionAbstract $context Context method or constructor or function.
     */
    public function __construct(
        protected \ReflectionParameter $parameter,
        protected \ReflectionFunctionAbstract $context
    ) {
        $name = $context->getName();
        if ($context instanceof \ReflectionMethod) {
            $name = $context->class . '::' . $name;
        }

        parent::__construct(\sprintf("Unable to resolve '%s' argument in '%s'", $parameter->name, $name));
    }

    public function getParameter(): \ReflectionParameter
    {
        return $this->parameter;
    }

    public function getContext(): \ReflectionFunctionAbstract
    {
        return $this->context;
    }
}
