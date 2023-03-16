<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Classes;

define('FOO', 'bar');

class ClassWithHeredoc
{
    public function __construct()
    {
        <<<class
FooBar
class;

        <<<class
class FooBar 
{

}
class;FOO;

        <<<'class'
class FooBar 
{

}
class ;FOO;
    }
}