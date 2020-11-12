<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Selective;

use Spiral\Attributes\Reader;
use Spiral\Attributes\ReaderInterface;

class SelectiveReader extends Reader
{
    /**
     * @var ReaderInterface[]
     */
    private $readers;

    /**
     * @param ReaderInterface[] $readers
     */
    public function __construct(array $readers)
    {
        $this->readers = $readers;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        return $this->resolve(static function (ReaderInterface $reader) use ($class, $name): iterable {
            return $reader->getClassMetadata($class, $name);
        });
    }

    /**
     * @psalm-param callable(ReaderInterface): list<array-key, object> $resolver
     *
     * @param callable $resolver
     * @return iterable
     */
    private function resolve(callable $resolver): iterable
    {
        foreach ($this->readers as $reader) {
            $result = $this->iterableToArray($resolver($reader));

            if (\count($result) > 0) {
                return $result;
            }
        }

        return [];
    }

    /**
     * @param \Traversable|array $result
     * @return array
     */
    private function iterableToArray(iterable $result): array
    {
        return $result instanceof \Traversable ? \iterator_to_array($result, false) : $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodMetadata(\ReflectionMethod $method, string $name = null): iterable
    {
        return $this->resolve(static function (ReaderInterface $reader) use ($method, $name): iterable {
            return $reader->getMethodMetadata($method, $name);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        return $this->resolve(static function (ReaderInterface $reader) use ($property, $name): iterable {
            return $reader->getPropertyMetadata($property, $name);
        });
    }
}
