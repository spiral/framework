<?php

declare(strict_types=1);

namespace Spiral\Events\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
#[NamedArgumentConstructor]
final class Listener
{
    /**
     * @param class-string|null $event
     */
    public function __construct(
        public readonly ?string $event = null,
        public ?string $method = null,
        public readonly int $priority = 0
    ) {
    }
}
