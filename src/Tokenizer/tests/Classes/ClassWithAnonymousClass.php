<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes;

class ClassWithAnonymousClass
{
    public function __construct()
    {
        $class = new class ('foo', 'bar') {
            private function someFunc(): void
            {
            }
        };
    }
}