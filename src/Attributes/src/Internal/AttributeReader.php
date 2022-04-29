<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

use Spiral\Attributes\Exception\SemanticAttributeException;
use Spiral\Attributes\Internal\Instantiator\Facade;
use Spiral\Attributes\Internal\Instantiator\InstantiatorInterface;
use Spiral\Attributes\Reader;

/**
 * @internal AttributeReader is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
abstract class AttributeReader extends Reader
{
    protected ContextRenderer $renderer;
    private InstantiatorInterface $instantiator;

    public function __construct(InstantiatorInterface $instantiator = null)
    {
        $this->instantiator = $instantiator ?? new Facade($this);
        $this->renderer = new ContextRenderer();
    }

    /**
     * @throws \Throwable
     */
    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        $attributes = $this->getClassAttributes($class, $name);

        foreach ($attributes as $attribute => $arguments) {
            yield $this->instantiator->instantiate($attribute, $arguments, $class);
        }

        foreach ($class->getTraits() as $trait) {
            yield from $this->getClassMetadata($trait, $name);
        }
    }

    /**
     * @throws \Throwable
     */
    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        $attributes = $this->getFunctionAttributes($function, $name);

        foreach ($attributes as $attribute => $arguments) {
            yield $this->instantiator->instantiate($attribute, $arguments, $function);
        }
    }

    /**
     * @throws \Throwable
     */
    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        $attributes = $this->getPropertyAttributes($property, $name);

        foreach ($attributes as $attribute => $arguments) {
            yield $this->instantiator->instantiate($attribute, $arguments, $property);
        }
    }

    /**
     * @throws \Throwable
     */
    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable
    {
        $attributes = $this->getConstantAttributes($constant, $name);

        foreach ($attributes as $attribute => $arguments) {
            yield $this->instantiator->instantiate($attribute, $arguments, $constant);
        }
    }

    /**
     * @throws \Throwable
     */
    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable
    {
        $attributes = $this->getParameterAttributes($parameter, $name);

        foreach ($attributes as $attribute => $arguments) {
            yield $this->instantiator->instantiate($attribute, $arguments, $parameter);
        }
    }

    protected function assertClassExists(string $class, \Reflector $context): void
    {
        if (!\class_exists($class)) {
            $message = \vsprintf('The metadata class "%s" in %s was not found', [
                $class,
                $this->renderer->render($context),
            ]);

            throw new SemanticAttributeException($message);
        }
    }

    /**
     * @return iterable<\ReflectionClass, array>
     */
    abstract protected function getClassAttributes(\ReflectionClass $class, ?string $name): iterable;

    /**
     * @return iterable<\ReflectionClass, array>
     */
    abstract protected function getFunctionAttributes(\ReflectionFunctionAbstract $function, ?string $name): iterable;

    /**
     * @return iterable<\ReflectionClass, array>
     */
    abstract protected function getPropertyAttributes(\ReflectionProperty $property, ?string $name): iterable;

    /**
     * @return iterable<\ReflectionClass, array>
     */
    abstract protected function getConstantAttributes(\ReflectionClassConstant $const, ?string $name): iterable;

    /**
     * @return iterable<\ReflectionClass, array>
     */
    abstract protected function getParameterAttributes(\ReflectionParameter $param, ?string $name): iterable;
}
