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
     * @param \ReflectionFunctionAbstract $function
     * @param string|null $name
     * @return object[]
     */
    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable;

    /**
     * @template T of object
     * @psalm-param class-string<T> $name
     * @psalm-return T
     *
     * @param \ReflectionFunctionAbstract $function
     * @param string $name
     * @return object|null
     */
    public function firstFunctionMetadata(\ReflectionFunctionAbstract $function, string $name): ?object;

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

    /**
     * @template T of object
     * @psalm-param class-string<T>|null $name
     * @psalm-return iterable<T>
     *
     * @param \ReflectionClassConstant $constant
     * @param string|null $name
     * @return object[]
     */
    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable;

    /**
     * @template T of object
     * @psalm-param class-string<T> $name
     * @psalm-return T
     *
     * @param \ReflectionClassConstant $constant
     * @param string $name
     * @return object|null
     */
    public function firstConstantMetadata(\ReflectionClassConstant $constant, string $name): ?object;

    /**
     * @template T of object
     * @psalm-param class-string<T>|null $name
     * @psalm-return iterable<T>
     *
     * @param \ReflectionParameter $parameter
     * @param string|null $name
     * @return object[]
     */
    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable;

    /**
     * @template T of object
     * @psalm-param class-string<T> $name
     * @psalm-return T
     *
     * @param \ReflectionParameter $parameter
     * @param string $name
     * @return object|null
     */
    public function firstParameterMetadata(\ReflectionParameter $parameter, string $name): ?object;
}
