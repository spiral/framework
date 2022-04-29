<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Key;

/**
 * A generator that returns a key containing information about the
 * time the file was last modified.
 *
 * @internal ModificationTimeKeyGenerator is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class ModificationTimeKeyGenerator implements KeyGeneratorInterface
{
    public function forClass(\ReflectionClass $class): string
    {
        if ($class->isUserDefined()) {
            return (string)\filemtime(
                $class->getFileName()
            );
        }

        return $class->getExtension()
            ->getVersion()
        ;
    }

    public function forProperty(\ReflectionProperty $prop): string
    {
        return $this->forClass(
            $prop->getDeclaringClass()
        );
    }

    public function forConstant(\ReflectionClassConstant $const): string
    {
        return $this->forClass(
            $const->getDeclaringClass()
        );
    }

    public function forFunction(\ReflectionFunctionAbstract $fn): string
    {
        if ($fn instanceof \ReflectionMethod) {
            return $this->forClass(
                $fn->getDeclaringClass()
            );
        }

        if ($fn->isUserDefined()) {
            return (string)\filemtime(
                $fn->getFileName()
            );
        }

        if ($extension = $fn->getExtension()) {
            return $extension->getVersion();
        }

        throw new \LogicException('Can not determine modification time of [' . $fn->getName() . ']');
    }

    public function forParameter(\ReflectionParameter $param): string
    {
        return $this->forFunction(
            $param->getDeclaringFunction()
        );
    }
}
