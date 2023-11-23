<?php

declare(strict_types=1);

namespace Spiral\Boot\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
class BootloaderRules
{
    public function __construct(
        public array $args = [],
        public bool $enabled = true,
        public array $allowEnv = [],
        public array $denyEnv = [],
        public bool $override = true,
    ) {
    }
}
