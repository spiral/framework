<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes\Targets;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetMethod;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetParameter;

class HomeController
{
    #[WithTargetMethod]
    public function index(): void
    {
    }

    #[WithTargetMethod]
    public function action(
        #[WithTargetParameter]
        string $name,
    ): void {
    }
}
