<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Value\Accessor;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Specification\Value;

class TrimAccessorTest extends TestCase
{
    /**
     * @dataProvider acceptsProvider
     * @param mixed $value
     * @param bool  $expected
     * @param bool  $expectedTrimmed
     */
    public function testAccepts($value, bool $expected, bool $expectedTrimmed): void
    {
        $int = new Value\IntValue();
        $trim = new Value\Accessor\Trim($int);
        $this->assertSame($expected, $int->accepts($value));
        $this->assertSame($expectedTrimmed, $trim->accepts($value));
    }

    /**
     * @return iterable
     */
    public function acceptsProvider(): iterable
    {
        return [
            [0, true, true],
            ['0', true, true],
            [1, true, true],
            ['1', true, true],
            [1.1, true, true],
            ['1.1', true, true],
            [-2, true, true],
            [-2.2, true, true],
            ['-2.2', true, true],
            ['', true, true],
            [' -2 ', false, true],
            [' ', false, true],
        ];
    }

    /**
     * @dataProvider convertProvider
     * @param mixed $value
     * @param mixed $expected
     */
    public function testConvert($value, $expected): void
    {
        $trim = new Value\Accessor\Trim(new Value\IntValue());
        $this->assertSame($expected, $trim->convert($value));
    }

    /**
     * @return iterable
     */
    public function convertProvider(): iterable
    {
        return [
            [0, 0],
            ['0', 0],
            [1, 1],
            ['1', 1],
            [1.1, 1],
            ['1.1', 1],
            [-2, -2],
            [' -2 ', -2],
            [-2.2, -2],
            ['-2.2', -2],
            ['', 0],
            [' ', 0],
        ];
    }
}
