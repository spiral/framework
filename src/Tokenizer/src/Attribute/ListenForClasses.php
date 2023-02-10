<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

/**
 * When applied to a listener, this attribute will instruct the tokenizer to listen for classes that are extending or
 * implementing the given class or uses attributes of the given class.
 */
#[\Attribute(\Attribute::TARGET_CLASS, \Attribute::IS_REPEATABLE), NamedArgumentConstructor]
final class ListenForClasses
{
    /**
     * @param class-string $target
     * @param non-empty-string|null $scope
     */
    public function __construct(
        public readonly string $target,
        public readonly ?string $scope = null
    ) {
    }
}
