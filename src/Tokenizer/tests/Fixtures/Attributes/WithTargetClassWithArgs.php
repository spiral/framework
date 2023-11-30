<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Fixtures\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class WithTargetClassWithArgs
{
    public function __construct(
        private string $foo,
        private string $bar,
    ) {
    }
}
