<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

interface ReaderInterface
{
    /**
     * @template T of object
     * @psalm-param class-string<T>|null $name
     * @psalm-return iterable<T>
     *
     * @param \ReflectionClass $class
     * @param string|null $name
     * @return object[]
     */
    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable;

    /**
     * @template T of object
     * @psalm-param class-string<T> $name
     * @psalm-return T
     *
     * @param \ReflectionClass $class
     * @param string $name
     * @return object|null
     */
    public function firstClassMetadata(\ReflectionClass $class, string $name): ?object;

    /**
     * @template T of object
     * @psalm-param class-string<T>|null $name
     * @psalm-return iterable<T>
     *
     * @param \ReflectionMethod $method
     * @param string|null $name
     * @return object[]
     */
    public function getMethodMetadata(\ReflectionMethod $method, string $name = null): iterable;

    /**
     * @template T of object
     * @psalm-param class-string<T> $name
     * @psalm-return T
     *
     * @param \ReflectionMethod $method
     * @param string $name
     * @return object|null
     */
    public function firstMethodMetadata(\ReflectionMethod $method, string $name): ?object;

    /**
     * @template T of object
     * @psalm-param class-string<T>|null $name
     * @psalm-return iterable<T>
     *
     * @param \ReflectionProperty $property
     * @param string|null $name
     * @return object[]
     */
    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable;

    /**
     * @template T of object
     * @psalm-param class-string<T> $name
     * @psalm-return T
     *
     * @param \ReflectionProperty $property
     * @param string $name
     * @return object|null
     */
    public function firstPropertyMetadata(\ReflectionProperty $property, string $name): ?object;
}
