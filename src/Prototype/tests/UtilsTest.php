<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Prototype\Utils;

class UtilsTest extends TestCase
{
    #[DataProvider('trailingProvider')]
    public function testTrimTrailingDigits(string $name, int $sequence, string $expected): void
    {
        $this->assertEquals($expected, Utils::trimTrailingDigits($name, $sequence));
    }

    public static function trailingProvider(): \Traversable
    {
        yield ['name7', 7, 'name',];
        yield ['name', 0, 'name',];
        yield ['name0', 0, 'name',];
        yield ['name1', 1, 'name'];
        yield ['name-1', 1, 'name-'];
        yield ['name-1', -1, 'name'];
    }

    #[DataProvider('injectValuesProvider')]
    public function testInjectValues(array $array, int $index, array $child, array $expected): void
    {
        $this->assertEquals($expected, Utils::injectValues($array, $index, $child));
    }

    public static function injectValuesProvider(): \Traversable
    {
        yield [
            ['a', 'b', 'c', 'd', 'e'],
            0,
            ['aa', 'bb'],
            ['aa', 'bb', 'a', 'b', 'c', 'd', 'e'],
        ];
        yield [
            ['a', 'b', 'c', 'd', 'e'],
            -2,
            ['aa', 'bb'],
            ['a', 'b', 'c', 'aa', 'bb', 'd', 'e'],
        ];
        yield [
            ['a', 'b', 'c', 'd', 'e'],
            2,
            ['aa', 'bb'],
            ['a', 'b', 'aa', 'bb', 'c', 'd', 'e'],
        ];
        yield [
            ['a', 'b', 'c', 'd', 'e'],
            5,
            ['aa', 'bb'],
            ['a', 'b', 'c', 'd', 'e', 'aa', 'bb'],
        ];
    }

    #[DataProvider('shortNameProvider')]
    public function testShortName(string $name, string $expected): void
    {
        $this->assertEquals($expected, Utils::shortName($name));
    }

    public static function shortNameProvider(): \Traversable
    {
        yield ['a\b\cdef', 'cdef'];
        yield ['abcdef', 'abcdef'];
        yield ['abcdef\\', ''];
    }
}
