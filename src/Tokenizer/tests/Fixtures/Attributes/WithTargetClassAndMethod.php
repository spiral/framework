<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Fixtures\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
final class WithTargetClassAndMethod
{
}
