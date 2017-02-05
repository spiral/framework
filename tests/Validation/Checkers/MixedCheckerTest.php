<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Core\Container;
use Spiral\Validation\Checkers\MixedChecker;
use Spiral\Validation\Validator;

class MixedCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider cardsProvider
     */
    public function testCardNumber($expected, $card)
    {
        $checker = new MixedChecker(new Container());

        $this->assertEquals($expected, $checker->cardNumber($card));
    }

    public function testMatch()
    {
        $checker = new MixedChecker(new Container());

        $mock = $this->getMockBuilder(Validator::class)->disableOriginalConstructor()->getMock();
        $mock->method('getValue')->with('abc')->will($this->returnValue(123));

        $checker = $checker->withValidator($mock);

        $this->assertTrue($checker->check('match', 123, ['abc']));
        $this->assertFalse($checker->check('match', 234, ['abc']));

        $this->assertTrue($checker->check('match', '123', ['abc']));
        $this->assertFalse($checker->check('match', '123', ['abc', true]));
    }

    public function cardsProvider()
    {
        return [
            [true, '122000000000003'],
            [false, '122000000010003'],
            [true, '34343434343434'],
            [false, '3434343434334'],
            [true, '5555555555554444'],
            [false, '555555555554444'],
            [true, '5019717010103742'],
            [false, '50197170103742'],
            [true, '36700102000000'],
            [false, '367001020010'],
            [true, '36148900647913'],
            [false, '36148900647933'],
            [true, '6011000400000000'],
            [false, '6011000400900000'],
            [true, '3528000700000000'],
            [false, '3528000707000000'],
            [false, 'abc'],
            [false, []],
        ];
    }
}
