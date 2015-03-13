<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Components\Tokenizer\Classes;

use Spiral\Tests\Cases\Components\Tokenizer\TestInterface;
use Spiral\Tests\Cases\Components\Tokenizer\TestTrait;

class ClassB extends ClassA implements TestInterface
{
    use TestTrait;
}