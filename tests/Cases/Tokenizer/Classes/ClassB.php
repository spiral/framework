<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Tokenizer\Classes;

use Spiral\Tests\Cases\Tokenizer\TestInterface;
use Spiral\Tests\Cases\Tokenizer\TestTrait;

class ClassB extends ClassA implements TestInterface
{
    use TestTrait;
}