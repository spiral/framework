<?php

declare(strict_types=1);

namespace Spiral\Events\Processor;

use Spiral\Events\Exception\InvalidArgumentException;

abstract class AbstractProcessor implements ProcessorInterface
{
    /**
     * @return class-string
     */
    protected function getEventFromTypeDeclaration(\ReflectionMethod $method): string
    {
        if (
            $method->getNumberOfParameters() > 1
            || !($type = $method->getParameters()[0]->getType()) instanceof \ReflectionNamedType
            || $type->isBuiltin()
        ) {
            throw $this->badClassMethod($method->class, $method->getName());
        }

        return $type->getName();
    }

    /**
     * @param class-string $class
     */
    protected function getMethod(string $class, string $name): \ReflectionMethod
    {
        try {
            return new \ReflectionMethod($class, $name);
        } catch (\ReflectionException) {
            throw $this->badClassMethod($class, $name);
        }
    }

    /**
     * @param class-string $class
     */
    private function badClassMethod(string $class, string $name): InvalidArgumentException
    {
        return new InvalidArgumentException(
            \sprintf(
                '`%s::%s` must contain only one parameter with event class type that listener will listen.',
                $class,
                $name
            )
        );
    }
}
