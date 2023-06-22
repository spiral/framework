<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Enums\Targets;

use Spiral\Tests\Tokenizer\Classes\Targets\ConsoleCommandInterface;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetClass;

#[WithTargetClass]
enum EnumWithAllTargets implements ConsoleCommandInterface
{
    case foo;
}
