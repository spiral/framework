<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes;

use Spiral\Tests\Tokenizer\Classes\Inner\ClassD;

class ClassWithNamedParameter
{
    public function __construct()
    {
        $this->foo(class: ClassD::class);
    }

    private function foo(string $class): void
    {
    }
}