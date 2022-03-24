<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

use Spiral\Attributes\Internal\Key\ConcatKeyGenerator;
use Spiral\Attributes\Internal\Key\HashKeyGenerator;
use Spiral\Attributes\Internal\Key\KeyGeneratorInterface;
use Spiral\Attributes\Internal\Key\ModificationTimeKeyGenerator;
use Spiral\Attributes\Internal\Key\NameKeyGenerator;
use Spiral\Attributes\ReaderInterface;

abstract class CachedReader extends Decorator
{
    protected KeyGeneratorInterface $key;

    public function __construct(ReaderInterface $reader, KeyGeneratorInterface $generator = null)
    {
        $this->key = $generator ?? $this->createDefaultKeyGenerator();

        parent::__construct($reader);
    }

    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        $result = $this->cached(
            $this->key->forClass($class),
            fn () => $this->iterableToArray(parent::getClassMetadata($class))
        );

        return $this->filter($name, $result);
    }

    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        $result = $this->cached(
            $this->key->forFunction($function),
            fn () => $this->iterableToArray(parent::getFunctionMetadata($function))
        );

        return $this->filter($name, $result);
    }

    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        $result = $this->cached(
            $this->key->forProperty($property),
            fn () => $this->iterableToArray(parent::getPropertyMetadata($property))
        );

        return $this->filter($name, $result);
    }

    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable
    {
        $result = $this->cached(
            $this->key->forConstant($constant),
            fn () => $this->iterableToArray(parent::getConstantMetadata($constant))
        );

        return $this->filter($name, $result);
    }

    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable
    {
        $result = $this->cached(
            $this->key->forParameter($parameter),
            fn () => $this->iterableToArray(parent::getParameterMetadata($parameter))
        );

        return $this->filter($name, $result);
    }

    protected function createDefaultKeyGenerator(): KeyGeneratorInterface
    {
        return new HashKeyGenerator(
            new ConcatKeyGenerator([
                new NameKeyGenerator(),
                new ModificationTimeKeyGenerator(),
            ])
        );
    }

    /**
     * @template T of object
     * @param callable(): array<T> $then
     * @return iterable<T>
     */
    abstract protected function cached(string $key, callable $then): iterable;

    /**
     * @template T of object
     * @param iterable<T> $attributes
     * @return array<T>
     */
    protected function iterableToArray(iterable $attributes): array
    {
        if ($attributes instanceof \Traversable) {
            return \iterator_to_array($attributes, false);
        }

        return $attributes;
    }
}
