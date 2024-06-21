<?php

declare(strict_types=1);

namespace Spiral\Console\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;
use Symfony\Component\Console\Input\InputOption;

#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Option
{
    /**
     * @param non-empty-string|null $name Option name. Property name by default
     * @param non-empty-string|array|null $shortcut Option shortcut
     * @param non-empty-string|null $description Option description
     * @param int<0, 31>|null $mode Option mode, {@see InputOption} constants
     * @param \Closure|array $suggestedValues Option suggested values
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly string|array|null $shortcut = null,
        public readonly ?string $description = null,
        public readonly ?int $mode = null,
        public readonly \Closure|array $suggestedValues = []
    ) {
    }
}
