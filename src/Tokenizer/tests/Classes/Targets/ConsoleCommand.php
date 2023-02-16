<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes\Targets;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetClass;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetConstant;

#[WithTargetClass]
class ConsoleCommand implements ConsoleCommandInterface
{
    #[WithTargetConstant]
    private const NAME = 'test';
}
