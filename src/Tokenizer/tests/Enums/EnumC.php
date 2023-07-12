<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Enums;

use Spiral\Tests\Tokenizer\Fixtures\TestInterface;
use Spiral\Tests\Tokenizer\Fixtures\TestTrait;

enum EnumC implements TestInterface
{
    use TestTrait;

    case C;
}
