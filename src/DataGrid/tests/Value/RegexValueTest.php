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

class RegexValueTest extends TestCase
{
    /**
     * @dataProvider acceptsProvider
     * @param string $pattern
     * @param mixed  $value
     * @param bool   $expected
     */
    public function testAccepts(string $pattern, $value, bool $expected): void
    {
        $regex = new Value\RegexValue($pattern);
        $this->assertSame($expected, $regex->accepts($value));
    }

    /**
     * @return iterable
     */
    public function acceptsProvider(): iterable
    {
        return [
            ['/.*/', 'abc', true],
            ['/.*/', 123, true],
            ['/.?/', '', true],
            ['/\d/', '', false],
            ['/\d/', 1, true],
            ['/[0-9]?/', 12, true],
        ];
    }
}
