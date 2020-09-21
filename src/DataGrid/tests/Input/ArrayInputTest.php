<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Input;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use Spiral\DataGrid\Input\ArrayInput;

class ArrayInputTest extends TestCase
{
    /**
     * @dataProvider hasValueProvider
     * @param string $option
     * @param bool   $expected
     */
    public function testHasValue(string $option, bool $expected): void
    {
        $input = new ArrayInput($this->data());
        $this->assertEquals($expected, $input->hasValue($option));
    }

    /**
     * @return iterable
     */
    public function hasValueProvider(): iterable
    {
        return [
            ['key1', true],
            ['Key1', true],
            ['key2', true],
            ['Key2', true],
            ['key 3', true],
            ['kEy 3', true],
            ['key4', false],
        ];
    }

    /**
     * @dataProvider getValueProvider
     * @param string $option
     * @param        $default
     * @param        $expected
     */
    public function testGetValue(string $option, $default, $expected): void
    {
        $input = new ArrayInput($this->data());
        $this->assertEquals($expected, $input->getValue($option, $default));
    }

    /**
     * @return iterable
     */
    public function getValueProvider(): iterable
    {
        return [
            ['key1', null, 'value1'],
            ['Key1', null, 'value1'],
            ['Key1', 'value2', 'value1'],

            ['key2', null, 'value2'],
            ['Key2', null, 'value2'],
            ['Key2', 'value1', 'value2'],

            ['key 3', null, ['value3']],
            ['kEy 3', null, ['value3']],
            ['kEy 3', 'value1', ['value3']],

            ['key4', null, null],
            ['key4', 'value1', 'value1'],
        ];
    }

    /**
     * @dataProvider namespaceProvider
     * @param string $namespace
     * @param array  $data
     * @throws ReflectionException
     */
    public function testWithNamespace(string $namespace, array $data): void
    {
        $input = new ArrayInput($this->data());
        $input = $input->withNamespace($namespace);

        $reflection = new ReflectionClass($input);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);

        $this->assertEquals($data, $property->getValue($input));
    }

    /**
     * @return iterable
     */
    public function namespaceProvider(): iterable
    {
        return [
            ['', $this->data()],
            ['  ', $this->data()],
            ['key1', []],
            [
                'namespace1',
                [
                    'key4'  => 'value4',
                    'Key5'  => 'value5',
                    'key 6' => ['value6'],
                ]
            ],
            [
                'Namespace1',
                [
                    'key4'  => 'value4',
                    'Key5'  => 'value5',
                    'key 6' => ['value6'],
                ]
            ],
            [
                'namespace 2',
                [
                    'key7'  => 'value7',
                    'Key8'  => 'value8',
                    'key 9' => ['value9'],
                ]
            ],
            [
                'nAmespace 2',
                [
                    'key7'  => 'value7',
                    'Key8'  => 'value8',
                    'key 9' => ['value9'],
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    private function data(): array
    {
        return [
            'key1'        => 'value1',
            'Key2'        => 'value2',
            'key 3'       => ['value3'],
            'namespace1'  => [
                'key4'  => 'value4',
                'Key5'  => 'value5',
                'key 6' => ['value6'],
            ],
            'Namespace 2' => [
                'key7'  => 'value7',
                'Key8'  => 'value8',
                'key 9' => ['value9'],
            ],
        ];
    }
}
