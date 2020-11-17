<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

use Spiral\Attributes\Reader;

abstract class AttributeReader extends Reader
{
    /**
     * @var Instantiator
     */
    private $instantiator;

    /**
     * @var Context
     */
    private $ctx;

    /**
     * AttributeReader constructor.
     */
    public function __construct()
    {
        $this->instantiator = new Instantiator();
        $this->ctx = new Context();
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        $attributes = $this->getClassAttributes($class, $name);
        $context = $this->ctx->getClassContext($class);

        foreach ($attributes as $attribute => $arguments) {
            yield $this->instantiate($attribute, $arguments, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        $attributes = $this->getFunctionAttributes($function, $name);
        $context = $this->ctx->getCallableContext($function);

        foreach ($attributes as $attribute => $arguments) {
            yield $this->instantiate($attribute, $arguments, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        $attributes = $this->getPropertyAttributes($property, $name);
        $context = $this->ctx->getPropertyContext($property);

        foreach ($attributes as $attribute => $arguments) {
            yield $this->instantiate($attribute, $arguments, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable
    {
        $attributes = $this->getConstantAttributes($constant, $name);
        $context = $this->ctx->getConstantContext($constant);

        foreach ($attributes as $attribute => $arguments) {
            yield $this->instantiate($attribute, $arguments, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable
    {
        $attributes = $this->getParameterAttributes($parameter, $name);
        $context = $this->ctx->getParameterContext($parameter);

        foreach ($attributes as $attribute => $arguments) {
            yield $this->instantiate($attribute, $arguments, $context);
        }
    }

    /**
     * @param \ReflectionClass $class
     * @param string|null $name
     * @return iterable<\ReflectionClass, array>
     */
    abstract protected function getClassAttributes(\ReflectionClass $class, ?string $name): iterable;

    /**
     * @param \ReflectionFunctionAbstract $function
     * @param string|null $name
     * @return iterable<\ReflectionClass, array>
     */
    abstract protected function getFunctionAttributes(\ReflectionFunctionAbstract $function, ?string $name): iterable;

    /**
     * @param \ReflectionProperty $property
     * @param string|null $name
     * @return iterable<\ReflectionClass, array>
     */
    abstract protected function getPropertyAttributes(\ReflectionProperty $property, ?string $name): iterable;

    /**
     * @param \ReflectionClassConstant $const
     * @param string|null $name
     * @return iterable<\ReflectionClass, array>
     */
    abstract protected function getConstantAttributes(\ReflectionClassConstant $const, ?string $name): iterable;

    /**
     * @param \ReflectionParameter $param
     * @param string|null $name
     * @return iterable<\ReflectionClass, array>
     */
    abstract protected function getParameterAttributes(\ReflectionParameter $param, ?string $name): iterable;

    /**
     * @return bool
     */
    protected function isNativeAttributesAvailable(): bool
    {
        return \version_compare(\PHP_VERSION, '8.0') >= 0;
    }

    /**
     * @param \ReflectionClass $attribute
     * @param array $arguments
     * @param string $context
     * @return object
     */
    private function instantiate(\ReflectionClass $attribute, array $arguments, string $context): object
    {
        return $this->instantiator->instantiate($attribute, $arguments, $context);
    }
}
