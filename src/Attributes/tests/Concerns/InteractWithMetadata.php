<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Concerns;

use Spiral\Attributes\ReaderInterface;

trait InteractWithMetadata
{
    use InteractWithReflection;

    abstract protected function getReader(): ReaderInterface;

    protected function getClassMetadata(string $class): iterable
    {
        $reader = $this->getReader();

        return $reader->getClassMetadata(
            $this->getReflectionClass($class)
        );
    }

    protected function getMethodMetadata(string $class, string $name): iterable
    {
        $reader = $this->getReader();

        return $reader->getFunctionMetadata(
            $this->getReflectionMethod($class, $name)
        );
    }

    protected function getFunctionMetadata(string $name): iterable
    {
        $reader = $this->getReader();

        return $reader->getFunctionMetadata(
            $this->getReflectionFunction($name)
        );
    }

    protected function getPropertyMetadata(string $class, string $name): iterable
    {
        $reader = $this->getReader();

        return $reader->getPropertyMetadata(
            $this->getReflectionProperty($class, $name)
        );
    }

    protected function getConstantMetadata(string $class, string $name): iterable
    {
        $reader = $this->getReader();

        return $reader->getConstantMetadata(
            $this->getReflectionConstant($class, $name)
        );
    }

    protected function getFunctionParameterMetadata(string $function, string $name): iterable
    {
        $reader = $this->getReader();

        return $reader->getParameterMetadata(
            $this->getReflectionFunctionParameter($function, $name)
        );
    }

    protected function getMethodParameterMetadata(string $class, string $method, string $name): iterable
    {
        $reader = $this->getReader();

        return $reader->getParameterMetadata(
            $this->getReflectionMethodParameter($class, $method, $name)
        );
    }
}
