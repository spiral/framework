<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes\Targets;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetParameter;

class ClassWithAttributeOnParameter
{
    public function action(
        #[WithTargetParameter]
        string $name,
    ): void {
    }
}
