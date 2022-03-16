<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

/**
 * @internal Context is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class ContextRenderer
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

    public function render(?\Reflector $reflector): string
    {
        return match (true) {
            $reflector instanceof \ReflectionClass => $this->renderClassContext($reflector),
            $reflector instanceof \ReflectionFunctionAbstract => $this->renderCallableContext($reflector),
            $reflector instanceof \ReflectionProperty => $this->renderPropertyContext($reflector),
            $reflector instanceof \ReflectionClassConstant => $this->renderConstantContext($reflector),
            $reflector instanceof \ReflectionParameter => $this->renderParameterContext($reflector),
            default => '<unknown>',
        };
    }

    public function renderClassContext(\ReflectionClass $class): string
    {
        if ($class->isAnonymous()) {
            return \sprintf(self::FORMAT_ANONYMOUS_CLASS, $class->getFileName(), $class->getStartLine());
        }

        return \sprintf(self::FORMAT_CLASS, $class->getName());
    }

    public function renderMethodContext(\ReflectionMethod $method): string
    {
        $class = $method->getDeclaringClass();

        return \sprintf(self::FORMAT_METHOD, $class->getName(), $method->getName());
    }

    public function renderFunctionContext(\ReflectionFunction $fn): string
    {
        if ($fn->isClosure()) {
            return \sprintf(self::FORMAT_ANONYMOUS_FUNCTION, $fn->getFileName(), $fn->getStartLine());
        }

        return \sprintf(self::FORMAT_FUNCTION, $fn->getName());
    }

    public function renderCallableContext(\ReflectionFunctionAbstract $function): string
    {
        if ($function instanceof \ReflectionMethod) {
            return $this->renderMethodContext($function);
        }

        if ($function instanceof \ReflectionFunction) {
            return $this->renderFunctionContext($function);
        }

        // Compatibility mode
        return \sprintf(self::FORMAT_FUNCTION, $function->getName());
    }

    public function renderPropertyContext(\ReflectionProperty $property): string
    {
        $class = $property->getDeclaringClass();

        return \sprintf(self::FORMAT_PROPERTY, $class->getName(), $property->getName());
    }

    public function renderConstantContext(\ReflectionClassConstant $const): string
    {
        $class = $const->getDeclaringClass();

        return \sprintf(self::FORMAT_CONSTANT, $class->getName(), $const->getName());
    }

    public function renderParameterContext(\ReflectionParameter $param): string
    {
        $context = $this->renderCallableContext($param->getDeclaringFunction());

        return \sprintf(self::FORMAT_PARAMETER, $param->getName(), $context);
    }
}
