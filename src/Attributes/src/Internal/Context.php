<?php

/**
 * This file is part of Attributes package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

/**
 * @internal Context is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
class Context
{
    /**
     * @var string
     */
    protected const FORMAT_CLASS = 'class %s';

    /**
     * @var string
     */
    protected const FORMAT_ANONYMOUS_CLASS = 'object(class@anonymous): %s(%d)';

    /**
     * @var string
     */
    protected const FORMAT_METHOD = 'method %s::%s()';

    /**
     * @var string
     */
    protected const FORMAT_PROPERTY = 'property %s::$%s';

    /**
     * @var string
     */
    protected const FORMAT_CONSTANT = 'constant %s::%s';

    /**
     * @var string
     */
    protected const FORMAT_FUNCTION = 'function %s()';

    /**
     * @var string
     */
    protected const FORMAT_ANONYMOUS_FUNCTION = 'object(Closure): %s(%d)';

    /**
     * @var string
     */
    protected const FORMAT_PARAMETER = 'parameter $%s of %s';

    /**
     * @param \ReflectionClass $class
     * @return string
     */
    public function getClassContext(\ReflectionClass $class): string
    {
        if ($class->isAnonymous()) {
            return \sprintf(static::FORMAT_ANONYMOUS_CLASS, $class->getFileName(), $class->getStartLine());
        }

        return \sprintf(static::FORMAT_CLASS, $class->getName());
    }

    /**
     * @param \ReflectionMethod $method
     * @return string
     */
    public function getMethodContext(\ReflectionMethod $method): string
    {
        $class = $method->getDeclaringClass();

        return \sprintf(static::FORMAT_METHOD, $class->getName(), $method->getName());
    }

    /**
     * @param \ReflectionFunction $fn
     * @return string
     */
    public function getFunctionContext(\ReflectionFunction $fn): string
    {
        if ($fn->isClosure()) {
            return \sprintf(self::FORMAT_ANONYMOUS_FUNCTION, $fn->getFileName(), $fn->getStartLine());
        }

        return \sprintf(self::FORMAT_FUNCTION, $fn->getName());
    }

    /**
     * @param \ReflectionFunctionAbstract $function
     * @return string
     */
    public function getCallableContext(\ReflectionFunctionAbstract $function): string
    {
        if ($function instanceof \ReflectionMethod) {
            return $this->getMethodContext($function);
        }

        if ($function instanceof \ReflectionFunction) {
            return $this->getFunctionContext($function);
        }

        // Compatibility mode
        return \sprintf(self::FORMAT_FUNCTION, $function->getName());
    }

    /**
     * @param \ReflectionProperty $property
     * @return string
     */
    public function getPropertyContext(\ReflectionProperty $property): string
    {
        $class = $property->getDeclaringClass();

        return \sprintf(self::FORMAT_PROPERTY, $class->getName(), $property->getName());
    }

    /**
     * @param \ReflectionClassConstant $const
     * @return string
     */
    public function getConstantContext(\ReflectionClassConstant $const): string
    {
        $class = $const->getDeclaringClass();

        return \sprintf(self::FORMAT_CONSTANT, $class->getName(), $const->getName());
    }

    /**
     * @param \ReflectionParameter $param
     * @return string
     */
    public function getParameterContext(\ReflectionParameter $param): string
    {
        $context = $this->getCallableContext($param->getDeclaringFunction());

        return \sprintf(self::FORMAT_PARAMETER, $param->getName(), $context);
    }
}
