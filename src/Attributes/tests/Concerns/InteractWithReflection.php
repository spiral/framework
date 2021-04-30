<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Concerns;

trait InteractWithReflection
{
    protected function getReflectionClass(string $class): \ReflectionClass
    {
        return new \ReflectionClass($class);
    }

    protected function getReflectionMethod(string $class, string $name): \ReflectionMethod
    {
        return $this->getReflectionClass($class)
            ->getMethod($name)
        ;
    }

    protected function getReflectionFunction(string $name): \ReflectionFunction
    {
        return new \ReflectionFunction($name);
    }

    protected function getReflectionProperty(string $class, string $name): \ReflectionProperty
    {
        return $this->getReflectionClass($class)
            ->getProperty($name)
        ;
    }

    protected function getReflectionConstant(string $class, string $name): \ReflectionClassConstant
    {
        $constants = $this->getReflectionClass($class)
            ->getReflectionConstants()
        ;

        foreach ($constants as $reflection) {
            if ($reflection->getName() === $name) {
                return $reflection;
            }
        }

        throw new \ReflectionException('Constant ' . $name . ' not found');
    }

    protected function getReflectionFunctionParameter(string $function, string $name): \ReflectionParameter
    {
        $parameters = $this->getReflectionFunction($function)
            ->getParameters()
        ;

        foreach ($parameters as $reflection) {
            if ($reflection->getName() === $name) {
                return $reflection;
            }
        }

        throw new \ReflectionException('Parameter ' . $name . ' not found');
    }

    protected function getReflectionMethodParameter(string $class, string $method, string $name): \ReflectionParameter
    {
        $parameters = $this->getReflectionMethod($class, $method)
            ->getParameters()
        ;

        foreach ($parameters as $reflection) {
            if ($reflection->getName() === $name) {
                return $reflection;
            }
        }

        throw new \ReflectionException('Parameter ' . $name . ' not found');
    }
}
