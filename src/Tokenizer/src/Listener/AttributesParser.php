<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\ListenForClasses;
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

        foreach ($this->reader->getClassMetadata($listener, ListenForClasses::class) as $attribute) {
            // Analyze the target class from ListenForClasses attribute.
            $refl = new \ReflectionClass($attribute->target);

            // Check if the target class has an attribute
            $attr = $refl->getAttributes(\Attribute::class)[0] ?? null;

            // If the target class has no attribute, then it's a normal class or interface.
            if ($attr === null) {
                yield new ListenerDefinition(
                    listenerClass: $listener->getName(),
                    target: $refl,
                    scope: $attribute->scope,
                );

                continue;
            }

            // Otherwise, it's an attribute class nad we need to pass the attribute instance to the listener.
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
