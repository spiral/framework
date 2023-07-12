<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Interfaces\Targets;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetMethod;

interface InterfaceWithAttributeOnMethod
{
    #[WithTargetMethod]
    public function foo();
}
