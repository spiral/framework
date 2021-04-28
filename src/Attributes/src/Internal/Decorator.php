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
use Spiral\Attributes\ReaderInterface;

/**
 * @internal Decorator is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
abstract class Decorator extends Reader
{
    /**
     * @var FallbackAttributeReader|NativeAttributeReader
     */
    private $reader;

    /**
     * @param ReaderInterface $reader
     */
    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
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
