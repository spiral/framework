<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Key;

/**
 * A generator that returns a unique key associated with the
 * passed reflection object.
 *
 * @internal NameKeyGenerator is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class NameKeyGenerator implements KeyGeneratorInterface
{
    /**
     * @var string
     */
    private const TPL_ANONYMOUS_CLASS = '{anonymous@class %s:%d:%d}';

    /**
     * @var string
     */
    private const TPL_ANONYMOUS_FN = '{anonymous@function %s:%d:%d}';

    /**
     * @var string
     */
    private const TPL_METHOD = '%s::%s()';

    /**
     * @var string
     */
    private const TPL_CONSTANT = '%s::%s';

    /**
     * @var string
     */
    private const TPL_PROPERTY = '%s::$%s';

    /**
     * @var string
     */
    private const TPL_PARAMETER = '%s($%s)';

    public function forClass(\ReflectionClass $class): string
    {
        if ($class->isAnonymous()) {
            return \vsprintf(self::TPL_ANONYMOUS_CLASS, [
                $class->getFileName(),
                $class->getStartLine(),
                $class->getEndLine(),
            ]);
        }

        return $class->getName();
    }

    public function forConstant(\ReflectionClassConstant $const): string
    {
        return \vsprintf(self::TPL_CONSTANT, [
            $this->forClass($const->getDeclaringClass()),
            $const->getName(),
        ]);
    }

    public function forProperty(\ReflectionProperty $prop): string
    {
        return \vsprintf(self::TPL_PROPERTY, [
            $this->forClass($prop->getDeclaringClass()),
            $prop->getName(),
        ]);
    }

    public function forParameter(\ReflectionParameter $param): string
    {
        return \vsprintf(self::TPL_PARAMETER, [
            $this->forFunction($param->getDeclaringFunction()),
            $param->getName(),
        ]);
    }

    public function forFunction(\ReflectionFunctionAbstract $fn): string
    {
        if ($fn instanceof \ReflectionMethod) {
            return \vsprintf(self::TPL_METHOD, [
                $this->forClass($fn->getDeclaringClass()),
                $fn->getName(),
            ]);
        }

        if ($fn->isClosure()) {
            return \vsprintf(self::TPL_ANONYMOUS_FN, [
                $fn->getFileName(),
                $fn->getStartLine(),
                $fn->getEndLine(),
            ]);
        }

        return $fn->getName();
    }
}
