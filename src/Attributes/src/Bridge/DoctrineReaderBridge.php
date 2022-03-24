<?php

declare(strict_types=1);

namespace Spiral\Attributes\Bridge;

use Doctrine\Common\Annotations\Reader;
use Spiral\Attributes\ReaderInterface;

/**
 * This bridge provides the ability to use the {@see ReaderInterface} in
 * doctrine-dependent ({@see Reader}) classes.
 *
 * For example in {@see \Doctrine\Common\Annotations\CachedReader} class.
 *
 * <code>
 *  //
 *  // Creating bridge
 *  //
 *  $bridge = new \Spiral\Attributes\Bridge\DoctrineReaderBridge(
 *      new \Spiral\Attributes\AttributeReader()
 *  );
 *
 *  //
 *  // Using bridge in doctrine-dependent class instead
 *  // of real doctrine class.
 *  //
 *  $doctrine = new \Doctrine\Common\Annotations\CachedReader($bridge, $cache);
 * </code>
 */
final class DoctrineReaderBridge implements Reader
{
    public function __construct(
        private readonly ReaderInterface $reader
    ) {
    }

    public function getClassAnnotations(\ReflectionClass $class): array
    {
        return $this->iterableToArray(
            $this->reader->getClassMetadata($class)
        );
    }

    public function getClassAnnotation(\ReflectionClass $class, $annotationName): ?object
    {
        return $this->reader->firstClassMetadata($class, $annotationName);
    }

    public function getMethodAnnotations(\ReflectionMethod $method): array
    {
        return $this->iterableToArray(
            $this->reader->getFunctionMetadata($method)
        );
    }

    public function getMethodAnnotation(\ReflectionMethod $method, $annotationName): ?object
    {
        return $this->reader->firstFunctionMetadata($method, $annotationName);
    }

    public function getPropertyAnnotations(\ReflectionProperty $property): array
    {
        return $this->iterableToArray(
            $this->reader->getPropertyMetadata($property)
        );
    }

    public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName): ?object
    {
        return $this->reader->firstPropertyMetadata($property, $annotationName);
    }

    /**
     * @param iterable<object> $meta
     * @return array<object>
     */
    private function iterableToArray(iterable $meta): array
    {
        if ($meta instanceof \Traversable) {
            return \iterator_to_array($meta, false);
        }

        return $meta;
    }
}
