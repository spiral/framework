<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation\Checkers;

use PHPUnit\Framework\TestCase;
use Spiral\Validation\Checker\MixedChecker;
use Spiral\Validation\ValidatorInterface;

class MixedTest extends TestCase
{
    /**
     * @dataProvider cardsProvider
     * @param bool  $expected
     * @param mixed $card
     */
    public function testCardNumber(bool $expected, $card): void
    {
        $checker = new MixedChecker();

        $this->assertEquals($expected, $checker->cardNumber($card));
    }

    public function testMatch(): void
    {
        $checker = new MixedChecker();

        $mock = $this->getMockBuilder(ValidatorInterface::class)->disableOriginalConstructor()->getMock();
        $mock->method('getValue')->with('abc')->willReturn(123);

        /** @var ValidatorInterface $mock */
        $this->assertTrue($checker->check($mock, 'match', 'field', 123, ['abc']));
        $this->assertFalse($checker->check($mock, 'match', 'field', 234, ['abc']));

        $this->assertTrue($checker->check($mock, 'match', 'field', '123', ['abc']));
        $this->assertFalse($checker->check($mock, 'match', 'field', '123', ['abc', true]));
    }

    public function cardsProvider(): array
    {
        return [
            [true, '122000000000003'],
            [false, '122000000010003'],
            [true, '34343434343434'],
            [false, '3434343434334'],
            [true, '5555555555554444'],
            [false, '5555 5555 5555 4444'],
            [false, '555555555554444'],
            [true, '5019717010103742'],
            [false, '5019 7170 1010 3742'],
            [false, '50197170103742'],
            [true, '36700102000000'],
            [false, '3670 0102 0000 00'],
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
