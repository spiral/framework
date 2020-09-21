<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid;

use LogicException;
use PHPUnit\Framework\TestCase;

use function Spiral\DataGrid\getValue;
use function Spiral\DataGrid\hasKey;
use function Spiral\DataGrid\hasValue;

class FunctionsTest extends TestCase
{
    /**
     * @dataProvider hasKeyProvider
     * @param mixed $key
     * @param bool  $expected
     */
    public function testHasKey($key, bool $expected): void
    {
        $data = [
            'key1' => 'value1',
            'Key2' => 'value2',
            3      => 'value3',
            '3'    => 'value3',
        ];

        $this->assertSame($expected, hasKey($data, $key));
    }

    /**
     * @return iterable
     */
    public function hasKeyProvider(): iterable
    {
        return [
            ['key1', true],
            ['kEy1', true],
            ['key2', true],
            ['Key2', true],
            ['keY2', true],
            [3, true],
            ['3', true],

            ['key 1', false],
        ];
    }

    /**
     * @dataProvider hasValueProvider
     * @param mixed $value
     * @param bool  $expected
     */
    public function testHasValue($value, bool $expected): void
    {
        $data = [
            'value1',
            'value2',
            '3',
        ];

        $this->assertSame($expected, hasValue($data, $value));
    }

    /**
     * @return iterable
     */
    public function hasValueProvider(): iterable
    {
        return [
            ['value1', true],
            ['Value1', true],
            ['value2', true],
            ['Value2', true],
            ['VALUE2', true],
            [3, true],
            ['3', true],

            ['value 1', false],
        ];
    }

    /**
     * @dataProvider getValueProvider
     * @param mixed       $key
     * @param string|null $expectException
     * @param mixed       $expected
     */
    public function testGetValue($key, ?string $expectException, $expected): void
    {
        $data = [
            'key1' => 'value1',
            'Key2' => 'value2',
        ];

        if ($expectException !== null) {
            $this->expectException($expectException);
        }

        $this->assertSame($expected, getValue($data, $key));
    }

    /**
     * @return iterable
     */
    public function getValueProvider(): iterable
    {
        return [
            ['key1', null, 'value1'],
            ['kEy1', null, 'value1'],
            ['key2', null, 'value2'],
            ['Key2', null, 'value2'],
            ['keY2', null, 'value2'],

            ['key 1', LogicException::class, null],
        ];
    }
}
