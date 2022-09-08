<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype;

use PHPUnit\Framework\TestCase;
use Spiral\Prototype\Utils;

class UtilsTest extends TestCase
{
    /**
     * @dataProvider trailingProvider
     *
     * @param string $name
     * @param int    $sequence
     * @param string $expected
     */
    public function testTrimTrailingDigits(string $name, int $sequence, string $expected): void
    {
        $this->assertEquals($expected, Utils::trimTrailingDigits($name, $sequence));
    }

    public function trailingProvider(): array
    {
        return [
            ['name7', 7, 'name',],
            ['name', 0, 'name',],
            ['name0', 0, 'name',],
            ['name1', 1, 'name'],
            ['name-1', 1, 'name-'],
            ['name-1', -1, 'name'],
        ];
    }

    /**
     * @dataProvider injectValuesProvider
     *
     * @param array $array
     * @param int   $index
     * @param array $child
     * @param array $expected
     */
    public function testInjectValues(array $array, int $index, array $child, array $expected): void
    {
        $this->assertEquals($expected, Utils::injectValues($array, $index, $child));
    }

    public function injectValuesProvider(): array
    {
        return [
            [
                ['a', 'b', 'c', 'd', 'e'],
                0,
                ['aa', 'bb'],
                ['aa', 'bb', 'a', 'b', 'c', 'd', 'e'],
            ],
            [
                ['a', 'b', 'c', 'd', 'e'],
                -2,
                ['aa', 'bb'],
                ['a', 'b', 'c', 'aa', 'bb', 'd', 'e'],
            ],
            [
                ['a', 'b', 'c', 'd', 'e'],
                2,
                ['aa', 'bb'],
                ['a', 'b', 'aa', 'bb', 'c', 'd', 'e'],
            ],
            [
                ['a', 'b', 'c', 'd', 'e'],
                5,
                ['aa', 'bb'],
                ['a', 'b', 'c', 'd', 'e', 'aa', 'bb'],
            ],
        ];
    }

    /**
     * @dataProvider shortNameProvider
     *
     * @param string $name
     * @param string $expected
     */
    public function testShortName(string $name, string $expected): void
    {
        $this->assertEquals($expected, Utils::shortName($name));
    }

    public function shortNameProvider(): array
    {
        return [
            ['a\b\cdef', 'cdef'],
            ['abcdef', 'abcdef'],
            ['abcdef\\', ''],
        ];
    }
}
