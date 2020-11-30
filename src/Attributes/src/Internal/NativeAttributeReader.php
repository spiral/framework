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

/**
 * @internal NativeAttributeReader is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
class NativeAttributeReader extends AttributeReader
{
    /**
     * NativeAttributeReader constructor.
     */
    public function __construct()
    {
        $this->checkAvailability();

        parent::__construct();
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
        return $this->format(
            $class->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getFunctionAttributes(\ReflectionFunctionAbstract $function, ?string $name): iterable
    {
        return $this->format(
            $function->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getPropertyAttributes(\ReflectionProperty $property, ?string $name): iterable
    {
        return $this->format(
            $property->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getConstantAttributes(\ReflectionClassConstant $const, ?string $name): iterable
    {
        return $this->format(
            $const->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getParameterAttributes(\ReflectionParameter $param, ?string $name): iterable
    {
        return $this->format(
            $param->getAttributes($name, \ReflectionAttribute::IS_INSTANCEOF)
        );
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
     * @param iterable<\ReflectionAttribute> $attributes
     * @return iterable<\ReflectionClass, array>
     * @throws \ReflectionException
     */
    private function format(iterable $attributes): iterable
    {
        foreach ($attributes as $attribute) {
            yield new \ReflectionClass($attribute->getName()) => $attribute->getArguments();
        }
    }
}
