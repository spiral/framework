<?php

declare(strict_types=1);

namespace Spiral\Events\Processor;

use Spiral\Events\Exception\InvalidArgumentException;

abstract class AbstractProcessor implements ProcessorInterface
{
    /**
     * Get event class list from the listener method signature.
     * The signature must have only one parameter with Event class type.
     *
     * @return class-string[]
     */
    protected function getEventFromTypeDeclaration(\ReflectionMethod $method): array
    {
        if ($method->getNumberOfParameters() > 1) {
            throw $this->badClassMethod($method->class, $method->getName());
        }
        $type = $method->getParameters()[0]->getType();

        /** @var \ReflectionNamedType[] $eventTypes */
        $eventTypes = match (true) {
            $type instanceof \ReflectionNamedType => [$type],
            $type instanceof \ReflectionUnionType => $type->getTypes(),
            default => throw $this->badClassMethod($method->class, $method->getName()),
        };

        $result = [];
        foreach ($eventTypes as $type) {
            if ($type->isBuiltin()) {
                continue;
            }
            $result[] = $type->getName();
        }

        return $result;
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
