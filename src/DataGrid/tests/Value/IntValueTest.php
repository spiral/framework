<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Value;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Specification\Value;
use stdClass;

class IntValueTest extends TestCase
{
    /**
     * @dataProvider acceptsProvider
     * @param mixed $value
     * @param bool  $expected
     */
    public function testAccepts($value, bool $expected): void
    {
        $int = new Value\IntValue();
        $this->assertSame($expected, $int->accepts($value));
    }

    /**
     * @return iterable
     */
    public function acceptsProvider(): iterable
    {
        return [
            [0, true],
            ['0', true],
            [1, true],
            ['1', true],
            [1.1, true],
            ['1.1', true],
            [-2, true],
            [-2.2, true],
            ['-2.2', true],
            ['', true],

            [false, false],
            ['false', false],
            [true, false],
            ['true', false],
            [null, false],
            ['null', false],
            [[], false],
            [new stdClass(), false],
        ];
    }

    /**
     * @dataProvider convertProvider
     * @param mixed $value
     * @param int   $expected
     */
    public function testConvert($value, int $expected): void
    {
        $int = new Value\IntValue();
        $this->assertSame($expected, $int->convert($value));
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
            [' 1 ', 1],
            [1.1, 1],
            ['1.1', 1],
            [-2, -2],
            [' -2 ', -2],
            [-2.2, -2],
            ['-2.2', -2],
            ['', 0],
        ];
    }
}
