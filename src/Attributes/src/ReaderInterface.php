<?php

declare(strict_types=1);

namespace Spiral\Attributes;

interface ReaderInterface
{
    /**
     * Gets a list of attributes and/or annotations applied to a class.
     *
     * @template T
     *
     * @param \ReflectionClass $class The reflection instance of the class from
     *      which the class annotations should be read.
     * @param class-string<T>|null $name The class name of the annotation
     *      and/or attribute.
     *
     * @return iterable<T> A list of class annotations and/or attributes.
     */
    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable;

    /**
     * Gets the attribute or annotation applied to a class.
     *
     * @template T
     *
     * @param \ReflectionClass $class The reflection instance of the class from
     *      which the class annotations should be read.
     * @param class-string<T> $name The class name of the annotation
     *      and/or attribute.
     *
     * @return T|null The annotation/attribute or {@see null}, if the requested
     *      annotation does not exist.
     */
    public function firstClassMetadata(\ReflectionClass $class, string $name): ?object;

    /**
     * Gets a list of attributes and/or annotations applied to a function
     * or method.
     *
     * @template T
     *
     * @param \ReflectionFunctionAbstract $function The reflection instance of
     *      the function or method from which the function annotations should
     *      be read.
     * @param class-string<T>|null $name The class name of the annotation
     *      and/or attribute.
     *
     * @return iterable<T> A list of function annotations and/or attributes.
     */
    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable;

    /**
     * Gets the attribute or annotation applied to a function or method.
     *
     * @template T
     *
     * @param \ReflectionFunctionAbstract $function The reflection instance of
     *      the function or method from which the function annotations should
     *      be read.
     * @param class-string<T> $name The class name of the annotation and/or
     *      attribute.
     *
     * @return T|null The annotation/attribute or {@see null}, if the requested
     *      annotation does not exist.
     */
    public function firstFunctionMetadata(\ReflectionFunctionAbstract $function, string $name): ?object;

    /**
     * Gets a list of attributes and/or annotations applied to a class property.
     *
     * @template T
     *
     * @param \ReflectionProperty $property The reflection instance of the
     *      property from which the property annotations should be read.
     * @param class-string<T>|null $name The class name of the annotation
     *      and/or attribute.
     *
     * @return iterable<T> A list of property annotations and/or attributes.
     */
    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable;

    /**
     * Gets the attribute or annotation applied to a property.
     *
     * @template T
     *
     * @param \ReflectionProperty $property The reflection instance of the
     *      property from which the property annotations should be read.
     * @param class-string<T> $name The class name of the annotation and/or
     *      attribute.
     *
     * @return T|null The annotation/attribute or {@see null}, if the requested
     *      annotation does not exist.
     */
    public function firstPropertyMetadata(\ReflectionProperty $property, string $name): ?object;

    /**
     * Gets a list of attributes and/or annotations applied to a class constant.
     *
     * @template T
     *
     * @param \ReflectionClassConstant $constant The reflection instance of the
     *      class constant from which the constant annotations should be read.
     * @param class-string<T>|null $name The class name of the annotation
     *      and/or attribute.
     *
     * @return iterable<T> A list of constant annotations and/or attributes.
     */
    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable;

    /**
     * Gets the attribute or annotation applied to a class constant.
     *
     * @template T
     *
     * @param \ReflectionClassConstant $constant The reflection instance of the
     *      class constant from which the constant annotations should be read.
     * @param class-string<T> $name The class name of the annotation and/or
     *      attribute.
     *
     * @return T|null The annotation/attribute or {@see null}, if the requested
     *      annotation does not exist.
     */
    public function firstConstantMetadata(\ReflectionClassConstant $constant, string $name): ?object;

    /**
     * Gets a list of attributes and/or annotations applied to a parameter of
     * a function or method.
     *
     * @template T
     *
     * @param \ReflectionParameter $parameter The reflection instance of the
     *      parameter from which the parameter annotations should be read.
     * @param class-string<T>|null $name The class name of the annotation
     *      and/or attribute.
     *
     * @return iterable<T> A list of parameter annotations and/or attributes.
     */
    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable;

    /**
     * Gets the attribute or annotation applied to a function's parameter.
     *
     * @template T
     *
     * @param \ReflectionParameter $parameter The reflection instance of the
     *      parameter from which the parameter annotations should be read.
     * @param class-string<T> $name The class name of the annotation and/or
     *      attribute.
     *
     * @return T|null The annotation/attribute or {@see null}, if the requested
     *      annotation does not exist.
     */
    public function firstParameterMetadata(\ReflectionParameter $parameter, string $name): ?object;
}
