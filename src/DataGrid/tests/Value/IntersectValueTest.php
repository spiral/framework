<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Value;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Specification\Value;

class IntersectValueTest extends TestCase
{
    /**
     * @dataProvider acceptsProvider
     * @param mixed $value
     * @param bool  $expected
     */
    public function testAccepts($value, bool $expected): void
    {
        $intersect = new Value\IntersectValue(new Value\StringValue(), '1', '2');
        $this->assertSame($expected, $intersect->accepts($value));
    }

    /**
     * @return iterable
     */
    public function acceptsProvider(): iterable
    {
        return [
            [1, true],
            ['1', true],
            ['3', false],
            [[], false],
            [[1], true],
            [['1'], true],
            [['1', '2'], true],
            [['1', 1], true],
            [['1', '3'], true],
        ];
    }
}
