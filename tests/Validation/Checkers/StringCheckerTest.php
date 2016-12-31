<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Validation\Checkers;


use Spiral\Core\Container;
use Spiral\Validation\Checkers\StringChecker;

class StringCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testShorter()
    {
        $checker = new StringChecker(new Container());

        $this->assertFalse($checker->shorter('abc', 2));
        $this->assertFalse($checker->shorter('абв', 2));

        $this->assertTrue($checker->shorter('abc', 3));
        $this->assertTrue($checker->shorter('абв', 3));

        $this->assertTrue($checker->shorter('abc', 4));
        $this->assertTrue($checker->shorter('абв', 4));
    }

    public function testLonger()
    {
        $checker = new StringChecker(new Container());

        $this->assertTrue($checker->longer('abc', 2));
        $this->assertTrue($checker->longer('абв', 2));

        $this->assertTrue($checker->longer('abc', 3));
        $this->assertTrue($checker->longer('абв', 3));

        $this->assertFalse($checker->longer('abc', 4));
        $this->assertFalse($checker->longer('абв', 4));
    }

    public function testLength()
    {
        $checker = new StringChecker(new Container());

        $this->assertTrue($checker->length('abc', 3));
        $this->assertTrue($checker->length('абв', 3));

        $this->assertFalse($checker->length('abc', 5));
        $this->assertFalse($checker->length('абв', 5));
    }

    public function testRange()
    {
        $checker = new StringChecker(new Container());

        $this->assertTrue($checker->range('abc', 2, 4));
        $this->assertTrue($checker->range('абв', 1, 100));

        $this->assertTrue($checker->range('abc', 0, 3));
        $this->assertTrue($checker->range('абв', 3, 20));

        $this->assertFalse($checker->range('abc', 5, 10));
        $this->assertFalse($checker->range('абв', 0, 2));
    }
}
