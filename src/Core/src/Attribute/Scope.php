<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Scope
{
    public function __construct(
        public string $name,
    ) {
    }
}
