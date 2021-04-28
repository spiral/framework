<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

use Spiral\Attributes\Exception\InitializationException;
use Spiral\Attributes\Exception\SemanticAttributeException;
use Spiral\Attributes\Internal\Instantiator\InstantiatorInterface;

/**
 * @internal NativeAttributeReader is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class NativeAttributeReader extends AttributeReader
{
    /**
     * @param InstantiatorInterface|null $instantiator
     */
    public function __construct(InstantiatorInterface $instantiator = null)
    {
        $this->checkAvailability();

        parent::__construct($instantiator);
    }

    /**
     * @return bool
     */
    public static function isAvailable(): bool
    {
        return \version_compare(\PHP_VERSION, '8.0') >= 0;
    }

    /**
     * {@inheritDoc}
     */
    protected function getClassAttributes(\ReflectionClass $class, ?string $name): iterable
    {
        return $this->format($class, $class->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF));
    }

    /**
     * {@inheritDoc}
     */
    protected function getFunctionAttributes(\ReflectionFunctionAbstract $function, ?string $name): iterable
    {
        return $this->format($function, $function->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF));
    }

    /**
     * {@inheritDoc}
     */
    protected function getPropertyAttributes(\ReflectionProperty $property, ?string $name): iterable
    {
        return $this->format($property, $property->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF));
    }

    /**
     * {@inheritDoc}
     */
    protected function getConstantAttributes(\ReflectionClassConstant $const, ?string $name): iterable
    {
        return $this->format($const, $const->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF));
    }

    /**
     * {@inheritDoc}
     */
    protected function getParameterAttributes(\ReflectionParameter $param, ?string $name): iterable
    {
        return $this->format($param, $param->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF));
    }

    /**
     * @return void
     */
    private function checkAvailability(): void
    {
        if (!self::isAvailable()) {
            throw new InitializationException('Requires the PHP >= 8.0');
        }
    }

    /**
     * @param \Reflector $context
     * @param iterable<\ReflectionAttribute> $attributes
     * @return iterable<\ReflectionClass, array>
     * @throws \ReflectionException
     */
    private function format(\Reflector $context, iterable $attributes): iterable
    {
        foreach ($attributes as $attribute) {
            $this->assertClassExists($attribute->getName(), $context);

            yield new \ReflectionClass($attribute->getName()) => $attribute->getArguments();
        }
    }
}
