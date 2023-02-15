<?php

declare(strict_types=1);

namespace Spiral\Console\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class AsCommand
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $help = null
    ) {
    }
}
