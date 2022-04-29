<?php

declare(strict_types=1);

namespace Spiral\Attributes;

abstract class Reader implements ReaderInterface
{
    public function firstClassMetadata(\ReflectionClass $class, string $name): ?object
    {
        foreach ($this->getClassMetadata($class, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    public function firstFunctionMetadata(\ReflectionFunctionAbstract $function, string $name): ?object
    {
        foreach ($this->getFunctionMetadata($function, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    public function firstPropertyMetadata(\ReflectionProperty $property, string $name): ?object
    {
        foreach ($this->getPropertyMetadata($property, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    public function firstConstantMetadata(\ReflectionClassConstant $constant, string $name): ?object
    {
        foreach ($this->getConstantMetadata($constant, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    public function firstParameterMetadata(\ReflectionParameter $parameter, string $name): ?object
    {
        foreach ($this->getParameterMetadata($parameter, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    /**
     * @template T of object
     *
     * @param class-string<T>|null $name
     * @param iterable<T|object> $annotations
     *
     * @psalm-return \Generator<int|mixed, T|object, mixed, void>
     */
    protected function filter(?string $name, iterable $annotations): \Generator
    {
        if ($name === null) {
            yield from $annotations;

            return;
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $name) {
                yield $annotation;
            }
        }
    }
}
