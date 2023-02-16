<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\ListenerDefinitionInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;

/**
 * @internal
 */
final class AttributesParser
{
    public function __construct(
        private readonly ReaderInterface $reader,
    ) {
    }

    /**
     * @return \Generator<ListenerDefinitionInterface>
     */
    public function parse(TokenizationListenerInterface $listener): \Generator
    {
        $listener = new \ReflectionClass($listener);

        foreach ($this->reader->getClassMetadata($listener) as $attribute) {
            if ($attribute instanceof ListenerDefinitionInterface) {
                yield $attribute;
            }
        }
    }
}
