<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Core\Container;
use Spiral\Validation\Checkers\NumberChecker;

class NumberCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testRange()
    {
        $checker = new NumberChecker(new Container());

        $this->assertTrue($checker->range(10, 1, 100));
        $this->assertTrue($checker->range(10, 10, 100));
        $this->assertTrue($checker->range(10, 1, 10));

        $this->assertTrue($checker->range(10.5, 1, 100));

        $this->assertFalse($checker->range(10, 11, 100));
        $this->assertFalse($checker->range(10, 10.1, 100));
        $this->assertFalse($checker->range(10, 1, 9.99));
    }

    public function testHigher()
    {
        $checker = new NumberChecker(new Container());

        $this->assertTrue($checker->higher(10, 10));
        $this->assertTrue($checker->higher(10, 9));
        $this->assertTrue($checker->higher(10, 9.99));

        $this->assertFalse($checker->higher(10, 11));
    }

    public function testLower()
    {
        $checker = new NumberChecker(new Container());

        $this->assertTrue($checker->lower(10, 11));
        $this->assertTrue($checker->lower(10, 10.01));

        $this->assertFalse($checker->lower(10, 9.99));
    }
}
