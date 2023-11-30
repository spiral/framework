<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes\Targets;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetClassWithArgs;

#[WithTargetClassWithArgs('foo', 'bar')]
class ClassWithAttributeWithArgsOnClass
{
}
