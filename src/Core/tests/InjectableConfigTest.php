<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Spiral\Tests\Core\Fixtures\TestConfig;
use Spiral\Core\Traits\Config\AliasTrait;

class InjectableConfigTest extends TestCase
{
    use AliasTrait;

    protected $config = [
        'aliases' => [
            'default' => 'value',
            'value'   => 'another',
            'another' => 'test'
        ]
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
        $this->assertInstanceOf(\ArrayIterator::class, $iterator);
        $this->assertSame($iterator->getArrayCopy(), $config->toArray());
    }

    /**
     *
     *
     *                           immutable by default
     */
    public function testWriteError(): void
    {
        $this->expectExceptionMessage("Unable to change configuration data, configs are treated as
                           immutable by default");
        $this->expectException(\Spiral\Core\Exception\ConfigException::class);
        $config = new TestConfig([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ]);

        $config['keyA'] = 'abc';
    }

    /**
     *
     *
     *                           immutable by default
     */
    public function testUnsetError(): void
    {
        $this->expectException(\Spiral\Core\Exception\ConfigException::class);// immutable by default
        $this->expectExceptionMessage("Unable to change configuration data, configs are treated as
                           immutable by default");
        $config = new TestConfig([
            'keyA' => 'value',
            'keyB' => 'valueB',
        ]);

        unset($config['keyA']);
    }

    public function testGetError(): void
    {
        $this->expectException(\Spiral\Core\Exception\ConfigException::class);
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
            ]
        ]));
    }

    public function testAliases(): void
    {
        $this->assertEquals('test', $this->resolveAlias('default'));
        $this->assertEquals('test', $this->resolveAlias('value'));
    }
}
