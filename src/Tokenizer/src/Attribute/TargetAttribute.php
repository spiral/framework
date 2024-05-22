<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Attribute;

use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\Factory;
use Spiral\Attributes\Internal\Instantiator\NamedArgumentsInstantiator;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Tokenizer\TokenizationListenerInterface;

/**
 * When applied to {@see TokenizationListenerInterface}, this attribute will instruct the tokenizer to listen for
 * classes that use attributes of the given class.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE), NamedArgumentConstructor]
final class TargetAttribute extends AbstractTarget
{
    /**
     * @param class-string $attribute
     * @param non-empty-string|null $scope Class locator scope
     * @param bool $namedArguments Whether to use named arguments when reading attributes
     * @param bool $scanParents Whether to scan parent classes/interfaces for attributes target of which is class
     */
    public function __construct(
        private readonly string $attribute,
        ?string $scope = null,
        private readonly bool $useAnnotations = false,
        private readonly bool $namedArguments = true,
        private readonly bool $scanParents = false,
    ) {
        parent::__construct($scope);
    }

    public function filter(array $classes): \Generator
    {
        $target = new \ReflectionClass($this->attribute);
        $attribute = $target->getAttributes(\Attribute::class)[0] ?? null;

        // If annotations are used, we need to use the annotation reader also
        // It will slow down the process a bit, but it will allow us to use annotations
        /** @psalm-suppress InternalClass */
        $reader = $this->useAnnotations
            ? (new Factory())->create()
            : new AttributeReader($this->namedArguments ? new NamedArgumentsInstantiator() : null);

        if ($attribute === null) {
            return;
        }

        $attribute = $attribute->newInstance();

        foreach ($classes as $class) {
            // If attribute is defined on class level and class has target attribute
            // then we can add it to the list of classes
            if ($attribute->flags & \Attribute::TARGET_CLASS) {
                if ($reader->firstClassMetadata($class, $target->getName())) {
                    yield $class->getName();
                    continue;
                }

                if ($this->scanParents) {
                    // Interfaces
                    foreach ($class->getInterfaces() as $interface) {
                        if ($reader->firstClassMetadata($interface, $target->getName())) {
                            yield $class->getName();
                            continue 2;
                        }
                    }

                    // Parents
                    $parent = $class->getParentClass();
                    while ($parent !== false) {
                        if ($reader->firstClassMetadata($parent, $target->getName())) {
                            yield $class->getName();
                            continue 2;
                        }
                        $parent = $parent->getParentClass();
                    }
                }
            }

            // If attribute is defined on method level and class methods has target attribute
            // then we can add it to the list of classes
            if ($attribute->flags & \Attribute::TARGET_METHOD) {
                foreach ($class->getMethods() as $method) {
                    if ($reader->firstFunctionMetadata($method, $target->getName())) {
                        yield $class->getName();
                        continue 2;
                    }
                }
            }

            // If attribute is defined on property level and class properties has target attribute
            // then we can add it to the list of classes
            if ($attribute->flags & \Attribute::TARGET_PROPERTY) {
                foreach ($class->getProperties() as $property) {
                    if ($reader->firstPropertyMetadata($property, $target->getName())) {
                        yield $class->getName();
                        continue 2;
                    }
                }
            }


            // If attribute is defined on constant level and class constants has target attribute
            // then we can add it to the list of classes
            if ($attribute->flags & \Attribute::TARGET_CLASS_CONSTANT) {
                foreach ($class->getReflectionConstants() as $constant) {
                    if ($reader->firstConstantMetadata($constant, $target->getName())) {
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
                        if ($reader->firstParameterMetadata($parameter, $target->getName())) {
                            yield $class->getName();
                            continue 3;
                        }
                    }
                }
            }
        }
    }
}
