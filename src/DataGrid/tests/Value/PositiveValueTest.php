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
use Spiral\DataGrid\Specification\Value\IntValue;
use Spiral\DataGrid\Specification\Value\PositiveValue;

class PositiveValueTest extends TestCase
{
    /**
     * @dataProvider acceptsProvider
     * @param mixed $value
     * @param bool  $expected
     */
    public function testAccepts($value, bool $expected): void
    {
        $positive = new PositiveValue(new IntValue());
        $nested = new PositiveValue($positive);
        $this->assertSame($expected, $positive->accepts($value));
        $this->assertSame($expected, $nested->accepts($value));
    }

    /**
     * @return iterable
     */
    public function acceptsProvider(): iterable
    {
        return [
            [1, true],
            ['1', true],
            [1.1, true],
            ['1.1', true],

            [0, false],
            ['0', false],
            [-1, false],
            ['-1', false],
            [-1.1, false],
            ['-1.1', false],
            ['', false],
        ];
    }
}
