<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Key;

/**
 * An interface for generating keys based on reflection objects.
 *
 * @internal KeyGeneratorInterface is an internal library interface, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
interface KeyGeneratorInterface
{
    /**
     * A method that returns a string key for the passed reflection
     * object of the PHP class.
     *
     * Please note that the method can accept the following objects:
     *  - {@see \ReflectionClass}
     *  - {@see \ReflectionObject}
     *
     * Including:
     * - Extension classes (not user defined {@see \ReflectionClass::isUserDefined()}).
     * - Anonymous classes (i.e. {@see \ReflectionClass::isAnonymous()}).
     *
     * @param \ReflectionClass $class
     */
    public function forClass(\ReflectionClass $class): string;

    /**
     * A method that returns a string key for the passed reflection
     * object of the PHP class property.
     */
    public function forProperty(\ReflectionProperty $prop): string;

    /**
     * A method that returns a string key for the passed reflection
     * object of the PHP class constant.
     *
     * The method can accept only objects of reflection of
     * class constants:
     *  - {@see \ReflectionClassConstant}
     *
     * @param \ReflectionClassConstant $const
     */
    public function forConstant(\ReflectionClassConstant $const): string;

    /**
     * A method that returns a string key for the passed reflection
     * object of the PHP function.
     *
     * The method can accept any function type:
     *  - {@see \ReflectionMethod}
     *  - {@see \ReflectionFunction}
     *  - {@see \ReflectionFunctionAbstract}
     *
     * Including:
     * - Extension functions (not user defined {@see \ReflectionFunctionAbstract::isUserDefined()}).
     * - Anonymous functions (i.e. {@see \ReflectionFunctionAbstract::isClosure()}).
     *
     * @param \ReflectionFunctionAbstract $fn
     */
    public function forFunction(\ReflectionFunctionAbstract $fn): string;

    /**
     * A method that returns a string key for the passed reflection
     * object of the PHP function's parameter.
     *
     * The method can accept any {@see \ReflectionParameter} of any
     * function types:
     *  - {@see \ReflectionMethod}
     *  - {@see \ReflectionFunction}
     *  - {@see \ReflectionFunctionAbstract}
     *
     * @param \ReflectionParameter $param
     */
    public function forParameter(\ReflectionParameter $param): string;
}
