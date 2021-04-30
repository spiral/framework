<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    /**
     * @var KeyGeneratorInterface
     */
    protected $key;

    /**
     * @param ReaderInterface $reader
     * @param KeyGeneratorInterface|null $generator
     */
    public function __construct(ReaderInterface $reader, KeyGeneratorInterface $generator = null)
    {
        $this->key = $generator ?? $this->createDefaultKeyGenerator();

        parent::__construct($reader);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        $result = $this->cached($this->key->forClass($class), function () use ($class) {
            return $this->iterableToArray(parent::getClassMetadata($class));
        });

        return $this->filter($name, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        $result = $this->cached($this->key->forFunction($function), function () use ($function) {
            return $this->iterableToArray(parent::getFunctionMetadata($function));
        });

        return $this->filter($name, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        $result = $this->cached($this->key->forProperty($property), function () use ($property) {
            return $this->iterableToArray(parent::getPropertyMetadata($property));
        });

        return $this->filter($name, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable
    {
        $result = $this->cached($this->key->forConstant($constant), function () use ($constant) {
            return $this->iterableToArray(parent::getConstantMetadata($constant));
        });

        return $this->filter($name, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable
    {
        $result = $this->cached($this->key->forParameter($parameter), function () use ($parameter) {
            return $this->iterableToArray(parent::getParameterMetadata($parameter));
        });

        return $this->filter($name, $result);
    }

    /**
     * @return KeyGeneratorInterface
     */
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
     * @param string $key
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
