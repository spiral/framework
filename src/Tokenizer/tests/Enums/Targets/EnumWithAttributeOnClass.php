<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Enums\Targets;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetClass;

#[WithTargetClass]
enum EnumWithAttributeOnClass
{
    case foo;
}
