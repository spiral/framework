<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\Traits\TargetTrait;

/**
 * @internal
 */
final class ClassLocatorByDefinition
{
    use TargetTrait;

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly ClassesInterface $classes,
        private readonly ScopedClassesInterface $scopedClasses,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getClasses(ListenerDefinition $definition): array
    {
        $classes = $this->findClasses($definition);

        $classes = $definition->attribute !== null
            ? $this->getClassesForAttribute($classes, $definition->target, $definition->attribute)
            : $this->getClassesForClass($classes, $definition->target);

        return \iterator_to_array($classes);
    }

    /**
     * Filter classes by target class or trait.
     *
     * @param array<class-string, \ReflectionClass> $classes
     * @return \Generator<class-string>
     */
    private function getClassesForClass(array $classes, \ReflectionClass $target): \Generator
    {
        foreach ($classes as $class) {
            if (!$target->isTrait()) {
                if ($class->isSubclassOf($target) || $class->getName() === $target->getName()) {
                    yield $class->getName();
                    continue;
                }
            }

            // Checking using traits
            if (\in_array($target->getName(), $this->fetchTraits($class->getName()))) {
                yield $class->getName();
            }
        }
    }

    /**
     * Filter classes by attribute.
     *
     * @param array<class-string, \ReflectionClass> $classes
     * @return \Generator<class-string>
     */
    private function getClassesForAttribute(array $classes, \ReflectionClass $target, \Attribute $attribute): \Generator
    {
        foreach ($classes as $class) {
            // If attribute is defined on class level and class has target attribute
            // then we can add it to the list of classes
            if (($attribute->flags & \Attribute::TARGET_CLASS)
                && $this->reader->firstClassMetadata($class, $target->getName())
            ) {
                yield $class->getName();
                continue;
            }

            // If attribute is defined on method level and class methods has target attribute
            // then we can add it to the list of classes
            if ($attribute->flags & \Attribute::TARGET_METHOD) {
                foreach ($class->getMethods() as $method) {
                    if ($this->reader->firstFunctionMetadata($method, $target->getName())) {
                        yield $class->getName();
                        continue 2;
                    }
                }
            }

            // If attribute is defined on property level and class properties has target attribute
            // then we can add it to the list of classes
            if ($attribute->flags & \Attribute::TARGET_PROPERTY) {
                foreach ($class->getProperties() as $property) {
                    if ($this->reader->firstPropertyMetadata($property, $target->getName())) {
                        yield $class->getName();
                        continue 2;
                    }
                }
            }


            // If attribute is defined on constant level and class constants has target attribute
            // then we can add it to the list of classes
            if ($attribute->flags & \Attribute::TARGET_CLASS_CONSTANT) {
                foreach ($class->getReflectionConstants() as $constant) {
                    if ($this->reader->firstConstantMetadata($constant, $target->getName())) {
                        yield $class->getName();
                        continue 2;
                    }
                }
            }


            // If attribute is defined on method parameters level and class method parameter has target attribute
            // then we can add it to the list of classes
            if ($attribute->flags & \Attribute::TARGET_PARAMETER) {
                foreach ($class->getMethods() as $method) {
                    foreach ($method->getParameters() as $parameter) {
                        if ($this->reader->firstParameterMetadata($parameter, $target->getName())) {
                            yield $class->getName();
                            continue 3;
                        }
                    }
                }
            }
        }
    }

    /**
     * @return \ReflectionClass[]
     */
    private function findClasses(ListenerDefinition $definition): array
    {
        // If scope for listener attribute is defined, we should use scoped class locator
        return $definition->scope !== null
            ? $this->scopedClasses->getScopedClasses($definition->scope)
            : $this->classes->getClasses();
    }
}
