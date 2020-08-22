<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Tokenizer\Classes;

use Spiral\Tests\Tokenizer\Fixtures\TestInterface;
use Spiral\Tests\Tokenizer\Fixtures\TestTrait;

class ClassB extends ClassA implements TestInterface
{
    use TestTrait;
}
