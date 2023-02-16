<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Attribute;

use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * When applied to a listener, this attribute will instruct the tokenizer to listen for classes that use attributes of
 * the given class.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE), NamedArgumentConstructor]
final class TargetAttribute implements ListenerDefinitionInterface
{
    /**
     * @param class-string $class
     * @param non-empty-string|null $scope
     */
    public function __construct(
        public readonly string $class,
        public readonly ?string $scope = null,
    ) {
    }

    public function filter(array $classes): \Generator
    {
        $target = new \ReflectionClass($this->class);
        $attribute = $target->getAttributes(\Attribute::class)[0] ?? null;
        $reader = new AttributeReader();

        if ($attribute === null) {
            return;
        }

        $attribute = $attribute->newInstance();

        foreach ($classes as $class) {
            // If attribute is defined on class level and class has target attribute
            // then we can add it to the list of classes
            if (($attribute->flags & \Attribute::TARGET_CLASS)
                && $reader->firstClassMetadata($class, $target->getName())
            ) {
                yield $class->getName();
                continue;
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

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function getCacheKey(): string
    {
        return \md5($this->class . $this->scope);
    }
}
