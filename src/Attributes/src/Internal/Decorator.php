<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

use Spiral\Attributes\Reader;
use Spiral\Attributes\ReaderInterface;

/**
 * @internal Decorator is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
abstract class Decorator extends Reader
{
    public function __construct(
        private readonly ReaderInterface $reader
    ) {
    }

    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        return $this->reader->getClassMetadata($class, $name);
    }

    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        return $this->reader->getFunctionMetadata($function, $name);
    }

    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        return $this->reader->getPropertyMetadata($property, $name);
    }

    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable
    {
        return $this->reader->getConstantMetadata($constant, $name);
    }

    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable
    {
        return $this->reader->getParameterMetadata($parameter, $name);
    }
}
