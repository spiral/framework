<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Interfaces\Targets;

use Spiral\Tests\Tokenizer\Classes\Targets\ConsoleCommandInterface;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetClass;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetConstant;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetMethod;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetParameter;

#[WithTargetClass]
interface InterfaceWithAllTargets extends ConsoleCommandInterface
{
    #[WithTargetConstant]
    const FOO = 'foo';

    #[WithTargetMethod]
    public function foo(
        #[WithTargetParameter]
        string $bar,
    );
}
