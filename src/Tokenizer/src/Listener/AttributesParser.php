<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\Attribute\TargetClass;
use Spiral\Tokenizer\TokenizationListenerInterface;

/**
 * @internal
 */
final class AttributesParser
{
    public function __construct(
        private readonly ReaderInterface $reader
    ) {
    }

    /**
     * @return \Generator<ListenerDefinition>
     */
    public function parse(TokenizationListenerInterface $listener): \Generator
    {
        $listener = new \ReflectionClass($listener);

        foreach ($this->reader->getClassMetadata($listener, TargetClass::class) as $attribute) {
            // Analyze the target class from TargetClass attribute.
            yield new ListenerDefinition(
                listenerClass: $listener->getName(),
                target: new \ReflectionClass($attribute->class),
                scope: $attribute->scope,
            );
        }

        foreach ($this->reader->getClassMetadata($listener, TargetAttribute::class) as $attribute) {
            // Analyze the target class from TargetAttribute attribute.
            $refl = new \ReflectionClass($attribute->class);

            // Check if the target class has an attribute
            $attr = $refl->getAttributes(\Attribute::class)[0] ?? null;

            if ($attr === null) {
                continue;
            }

            // It helps to understand where the target attribute class is used (class, method, property, ...).
            yield new ListenerDefinition(
                listenerClass: $listener->getName(),
                target: $refl,
                scope: $attribute->scope,
                attribute: $attr->newInstance()
            );
        }
    }
}
