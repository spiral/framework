<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes\Targets;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetConstant;

class ClassWithAttributeOnConstant
{
    use SomeTrait;

    #[WithTargetConstant]
    private const NAME = 'test';
}
