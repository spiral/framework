<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Value;

use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Specification\Value;
use Spiral\DataGrid\Specification\ValueInterface;

class NotEmptyTest extends TestCase
{
    /**
     * @dataProvider acceptsProvider
     * @param mixed               $value
     * @param bool                $expected
     * @param ValueInterface|null $base
     */
    public function testAccepts($value, bool $expected, ?ValueInterface $base = null): void
    {
        $notEmpty = new Value\NotEmpty($base);
        $this->assertSame($expected, $notEmpty->accepts($value));
    }

    /**
     * @return iterable
     */
    public function acceptsProvider(): iterable
    {
        $emptyString = new Value\StringValue(true);
        $notEmptyString = new Value\StringValue(false);
        $int = new Value\IntValue();

        return [
            ['', false],
            [null, false],
            [false, false],
            ['0', false],
            [0, false],
            ['', false, $emptyString],
            ['', false, $notEmptyString],
            ['', false, $int],

            [' ', true],
            ['1', true],
            [1, true],
            [true, true],
            ['1', true, $emptyString],
            ['1', true, $notEmptyString],
            ['1', true, $int],
        ];
    }

    /**
     * @dataProvider convertProvider
     * @param mixed               $value
     * @param int                 $expected
     * @param ValueInterface|null $base
     */
    public function testConvert($value, $expected, ?ValueInterface $base = null): void
    {
        $notEmpty = new Value\NotEmpty($base);
        $this->assertSame($expected, $notEmpty->convert($value));
    }

    /**
     * @return iterable
     */
    public function convertProvider(): iterable
    {
        return [
            ['', ''],
            [' ', ' '],
            [null, null],
            [false, false],
            [true, true],
            ['1', '1'],
            ['1', 1, new Value\IntValue()],
        ];
    }

    public function testNotEmpty(): void
    {
        $notEmpty = new Value\NotEmpty(new Value\IntValue());
        $this->assertTrue($notEmpty->accepts('1'));
        $this->assertFalse($notEmpty->accepts(''));
        $this->assertSame(0, $notEmpty->convert(''));

        $notEmpty = new Value\NotEmpty(new Value\DatetimeValue());
        $this->assertTrue($notEmpty->accepts('now'));
        $this->assertFalse($notEmpty->accepts(''));
        $this->assertInstanceOf(DateTimeInterface::class, $notEmpty->convert(''));
    }
}
