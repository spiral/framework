<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Core;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Exception\ConfigException;
use Spiral\Core\Traits\Config\AliasTrait;
use Spiral\Tests\Core\Fixtures\TestConfig;

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

        $this->assertArrayHasKey('key', $config);
        $this->assertEquals('value', $config['key']);

        $this->assertArrayNotHasKey('otherKey', $config);
    }

    public function testToArray(): void
    {
        $config = new TestConfig([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ]);

        $this->assertEquals([
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
        $this->assertInstanceOf(ArrayIterator::class, $iterator);
        $this->assertSame($iterator->getArrayCopy(), $config->toArray());
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

    /**
     * @covers \Spiral\Core\InjectableConfig::__set_state()
     */
    public function testSerialize(): void
    {
        $config = new TestConfig([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ]);

        $serialized = serialize($config);
        $this->assertEquals($config, unserialize($serialized));

        $this->assertEquals($config, TestConfig::__set_state([
            'config' => [
                'keyA' => 'value',
                'keyB' => 'valueB',
            ],
        ]));
    }

    public function testAliases(): void
    {
        $this->assertEquals('test', $this->resolveAlias('default'));
        $this->assertEquals('test', $this->resolveAlias('value'));
    }
}
