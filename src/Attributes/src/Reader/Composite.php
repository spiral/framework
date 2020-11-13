<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Reader;

use Spiral\Attributes\ReaderInterface;

abstract class Composite extends Reader
{
    /**
     * @var ReaderInterface[]
     */
    protected $readers;

    /**
     * @param ReaderInterface[] $readers
     */
    public function __construct(iterable $readers)
    {
        $this->readers = $this->iterableToArray($readers);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        return $this->each(static function (ReaderInterface $reader) use ($class, $name): iterable {
            return $reader->getClassMetadata($class, $name);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        return $this->each(static function (ReaderInterface $reader) use ($function, $name): iterable {
            return $reader->getFunctionMetadata($function, $name);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        return $this->each(static function (ReaderInterface $reader) use ($property, $name): iterable {
            return $reader->getPropertyMetadata($property, $name);
        });
    }

    /**
     * @psalm-param callable(ReaderInterface): list<array-key, object> $resolver
     *
     * @param callable $resolver
     * @return iterable
     */
    abstract protected function each(callable $resolver): iterable;

    /**
     * @param \Traversable|array $result
     * @return array
     */
    protected function iterableToArray(iterable $result): array
    {
        return $result instanceof \Traversable ? \iterator_to_array($result, false) : $result;
    }
}
