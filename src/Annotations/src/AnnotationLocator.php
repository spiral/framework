<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Annotations;

use Spiral\Attributes\AnnotationReader;
use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Tokenizer\ClassesInterface;

/**
 * Locate all available annotations for methods, classes and properties across all the codebase.
 *
 * @deprecated since v2.9. Please, use combination of {@see ClassesInterface} and {@see ReaderInterface}
 */
final class AnnotationLocator implements SingletonInterface
{
    /** @var ClassesInterface */
    private $classLocator;

    /** @var ReaderInterface */
    private $reader;

    /** @var array */
    private $targets = [];

    /**
     * AnnotationLocator constructor.
     *
     * @param ReaderInterface|null $reader
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(ClassesInterface $classLocator, ReaderInterface $reader = null)
    {
        $this->classLocator = $classLocator;
        $this->reader = $reader ?? (new Factory())->create();
    }

    /**
     * Limit locator to only specific class types.
     */
    public function withTargets(array $targets): self
    {
        $locator = clone $this;
        $locator->targets = $targets;

        return $locator;
    }

    /**
     * Find all classes with given annotation.
     *
     * @return iterable|AnnotatedClass[]
     */
    public function findClasses(string $annotation): iterable
    {
        foreach ($this->getTargets() as $target) {
            $found = $this->reader->firstClassMetadata($target, $annotation);
            if ($found !== null) {
                yield new AnnotatedClass($target, $found);
            }
        }
    }

    /**
     * Find all methods with given annotation.
     *
     * @return iterable|AnnotatedMethod[]
     */
    public function findMethods(string $annotation): iterable
    {
        foreach ($this->getTargets() as $target) {
            foreach ($target->getMethods() as $method) {
                $found = $this->reader->firstFunctionMetadata($method, $annotation);
                if ($found !== null) {
                    yield new AnnotatedMethod($method, $found);
                }
            }
        }
    }

    /**
     * Find all properties with given annotation.
     *
     * @return iterable|AnnotatedProperty[]
     */
    public function findProperties(string $annotation): iterable
    {
        foreach ($this->getTargets() as $target) {
            foreach ($target->getProperties() as $property) {
                $found = $this->reader->firstPropertyMetadata($property, $annotation);
                if ($found !== null) {
                    yield new AnnotatedProperty($property, $found);
                }
            }
        }
    }

    /**
     * @return iterable|\ReflectionClass[]
     */
    private function getTargets(): iterable
    {
        if ($this->targets === []) {
            yield from $this->classLocator->getClasses();
            return;
        }

        foreach ($this->targets as $target) {
            yield from $this->classLocator->getClasses($target);
        }
    }
}
