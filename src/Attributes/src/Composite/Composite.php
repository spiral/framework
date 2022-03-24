<?php

declare(strict_types=1);

namespace Spiral\Attributes\Composite;

use Spiral\Attributes\Reader;
use Spiral\Attributes\ReaderInterface;

abstract class Composite extends Reader
{
    /**
     * @var ReaderInterface[]
     */
    protected array $readers;

    /**
     * @param ReaderInterface[] $readers
     */
    public function __construct(iterable $readers)
    {
        $this->readers = $this->iterableToArray($readers);
    }

    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        return $this->each(static fn (ReaderInterface $reader): iterable => $reader->getClassMetadata($class, $name));
    }

    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        return $this->each(
            static fn (ReaderInterface $reader): iterable => $reader->getFunctionMetadata($function, $name)
        );
    }

    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        return $this->each(
            static fn (ReaderInterface $reader): iterable => $reader->getPropertyMetadata($property, $name)
        );
    }

    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable
    {
        return $this->each(
            static fn (ReaderInterface $reader): iterable => $reader->getConstantMetadata($constant, $name)
        );
    }

    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable
    {
        return $this->each(
            static fn (ReaderInterface $reader): iterable => $reader->getParameterMetadata($parameter, $name)
        );
    }


    /**
     * @param callable(ReaderInterface): list<array-key, object> $resolver
     */
    abstract protected function each(callable $resolver): iterable;

    /**
     * @param \Traversable|array $result
     */
    protected function iterableToArray(iterable $result): array
    {
        return $result instanceof \Traversable ? \iterator_to_array($result, false) : $result;
    }
}
