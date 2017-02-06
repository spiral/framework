<?php

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Core\Container;
use Spiral\Tests\Validation\Fixtures\SimpleTestChecker;

class SimpleCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testTest()
    {
        $checker = new SimpleTestChecker(new Container());

        $this->assertFalse($checker->test('test2'));
        $this->assertFalse($checker->test(''));
        $this->assertFalse($checker->test(false));
        $this->assertFalse($checker->test(true));
        $this->assertFalse($checker->test(0));

        $this->assertTrue($checker->test('test'));
    }

    public function testString()
    {
        $checker = new SimpleTestChecker(new Container());

        $this->assertFalse($checker->string('string2', 'string'));
        $this->assertFalse($checker->string('', 'string'));
        $this->assertFalse($checker->string(false, 'string'));
        $this->assertFalse($checker->string(true, 'string'));
        $this->assertFalse($checker->string(0, 'string'));

        $this->assertTrue($checker->string('string', 'string'));
    }
}
