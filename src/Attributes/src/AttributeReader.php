<?php

/**
 * This file is part of Attributes package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

use Spiral\Attributes\Internal\FallbackAttributeReader;
use Spiral\Attributes\Internal\NativeAttributeReader;

final class AttributeReader extends Reader
{
    /**
     * @var FallbackAttributeReader|NativeAttributeReader
     */
    private $reader;

    /**
     * AttributeReader constructor.
     */
    public function __construct()
    {
        $this->reader = NativeAttributeReader::isAvailable()
            ? new NativeAttributeReader()
            : new FallbackAttributeReader();
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        return $this->reader->getClassMetadata($class, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        return $this->reader->getFunctionMetadata($function, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        return $this->reader->getPropertyMetadata($property, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable
    {
        return $this->reader->getConstantMetadata($constant, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable
    {
        return $this->reader->getParameterMetadata($parameter, $name);
    }
}
