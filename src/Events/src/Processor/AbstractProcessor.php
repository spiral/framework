<?php

declare(strict_types=1);

namespace Spiral\Events\Processor;

use Spiral\Events\Exception\InvalidArgumentException;

abstract class AbstractProcessor implements ProcessorInterface
{
    protected function getEventFromTypeDeclaration(\ReflectionMethod $method): string
    {
        if (
            $method->getNumberOfParameters() > 1 ||
            !($type = $method->getParameters()[0]->getType()) instanceof \ReflectionNamedType ||
            $type->isBuiltin()
        ) {
            throw new InvalidArgumentException(\sprintf(
                'The %s must accept the Event as parameter in the method %s.', $method->class, $method->getName()
            ));
        }

        return $type->getName();
    }

    protected function getMethod(string $class, string $name): \ReflectionMethod
    {
        try {
            return new \ReflectionMethod($class, $name);
        } catch (\ReflectionException) {
            throw new InvalidArgumentException(\sprintf(
                'The %s class must accept the Event as parameter in the method %s.', $class, $name
            ));
        }
    }
}
