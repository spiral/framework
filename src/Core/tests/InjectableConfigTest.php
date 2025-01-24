<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Exception\ConfigException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Traits\Config\AliasTrait;
use Spiral\Tests\Core\Fixtures\IntKeysConfig;
use Spiral\Tests\Core\Fixtures\TestConfig;

#[\PHPUnit\Framework\Attributes\CoversClass(\Spiral\Core\InjectableConfig::class)]
class InjectableConfigTest extends TestCase
{
    use AliasTrait;

    protected $config = [
        'aliases' => [
            'default' => 'value',
            'value'   => 'another',
            'another' => 'test',
        ],
    ];

    public function testArrayAccess(): void
    {
        $config = new TestConfig([
            'key' => 'value',
        ]);

        self::assertArrayHasKey('key', $config);
        self::assertEquals('value', $config['key']);

        self::assertArrayNotHasKey('otherKey', $config);
    }

    public function testToArray(): void
    {
        $config = new TestConfig([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ]);

        self::assertSame([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ], $config->toArray());
    }

    public function testIteration(): void
    {
        $config = new TestConfig([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ]);

        $iterator = $config->getIterator();
        self::assertInstanceOf(\ArrayIterator::class, $iterator);
        self::assertSame($iterator->getArrayCopy(), $config->toArray());
    }

    public function testWriteError(): void
    {
        $excepted = 'Unable to change configuration data, configs are treated as immutable by default';
        $this->expectExceptionMessage($excepted);

        $this->expectException(ConfigException::class);
        $config = new TestConfig([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ]);

        $config['keyA'] = 'abc';
    }

    public function testUnsetError(): void
    {
        $excepted = 'Unable to change configuration data, configs are treated as immutable by default';
        $this->expectExceptionMessage($excepted);

        $config = new TestConfig([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ]);

        unset($config['keyA']);
    }

    public function testGetError(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("Undefined configuration key 'keyC'");

        $config = new TestConfig([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ]);

        $config['keyC'];
    }

    public function testSerialize(): void
    {
        $config = new TestConfig([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ]);

        $serialized = serialize($config);
        self::assertEquals($config, unserialize($serialized));

        self::assertEquals($config, TestConfig::__set_state([
            'config' => [
                'keyA' => 'value',
                'keyB' => 'valueB',
            ],
        ]));
    }

    public function testAliases(): void
    {
        self::assertSame('test', $this->resolveAlias('default'));
        self::assertSame('test', $this->resolveAlias('value'));
    }

    public function testCircleReference(): void
    {
        self::expectException(ContainerException::class);
        self::expectExceptionMessage('Circle reference detected for alias `foo`');

        $config = new TestConfig([
            'key' => 'value',
            'aliases' => [
                'foo' => 'bar',
                'bar' => 'foo',
            ],
        ]);

        $config->resolveAlias('foo');
    }

    public function testConfigWithIntKeys(): void
    {
        $config = new IntKeysConfig([10 => 'value']);

        self::assertSame([
            10 => 'value',
            1 => 'some',
            3 => 'other',
            7 => 'key',
        ], $config->toArray());
    }
}
