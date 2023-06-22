<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Interfaces\Targets;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetParameter;

interface InterfaceWithAttributeOnParameter
{
    public function foo(
        #[WithTargetParameter]
        string $bar,
    );
}
