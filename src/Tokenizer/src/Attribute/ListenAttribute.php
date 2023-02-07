<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_CLASS, \Attribute::IS_REPEATABLE), NamedArgumentConstructor]
final class ListenAttribute
{
    /**
     * @param class-string $class
     */
    public function __construct(
        public readonly string $class,
    ) {
    }
}
