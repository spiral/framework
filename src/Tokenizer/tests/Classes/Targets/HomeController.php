<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes\Targets;

use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetMethod;

class HomeController
{
    #[WithTargetMethod]
    public function index(): void
    {
    }

    #[WithTargetMethod]
    public function action(): void
    {
    }
}
