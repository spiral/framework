<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Enums\Targets;

use Spiral\Tests\Tokenizer\Classes\Targets\SomeTrait;

enum EnumWithTrait
{
    use SomeTrait;

    case foo;
}
