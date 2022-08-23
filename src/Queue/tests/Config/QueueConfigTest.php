<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\DefaultSerializer;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Queue\PhpSerializer;
use Spiral\Tests\Queue\TestCase;

final class QueueConfigTest extends TestCase
{
    public function testGetsAliases(): void
    {
        $config = new QueueConfig([
            'aliases' => ['foo', 'bar'],
        ]);

        $this->assertSame(['foo', 'bar'], $config->getAliases());
    }

    public function testGetNotExistsAliases(): void
    {
        $config = new QueueConfig();

        $this->assertSame([], $config->getAliases());
    }

    public function testGetsDefaultDriver(): void
    {
        $config = new QueueConfig([
            'default' => 'foo',
        ]);
        $this->assertSame('foo', $config->getDefaultDriver());
    }

    public function testGetsEmptyDefaultDriverShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Default queue connection is not defined.');

        $config = new QueueConfig();

        $config->getDefaultDriver();
    }

    public function testGetsNonStringDefaultDriverShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Default queue connection config value must be a string');

        $config = new QueueConfig(['default' => ['foo']]);

        $config->getDefaultDriver();
    }

    public function testGetsDriverAliases(): void
    {
        $config = new QueueConfig([
            'driverAliases' => ['foo', 'bar'],
        ]);

        $this->assertSame(['foo', 'bar'], $config->getDriverAliases());
    }

    public function testGetNotExistsDriverAliases(): void
    {
        $config = new QueueConfig();

        $this->assertSame([], $config->getDriverAliases());
    }

    public function testGetsConnectionsWithoutDriver(): void
    {
        $config = new QueueConfig([
            'connections' => ['foo', 'bar'],
        ]);

        $this->assertSame(['foo', 'bar'], $config->getConnections());
    }

    public function testGetsNotExistsConnections(): void
    {
        $config = new QueueConfig();

        $this->assertSame([], $config->getConnections());
    }

    public function testGetsConnectionsWithSpecificDriverAlias(): void
    {
        $config = new QueueConfig([
            'connections' => [
                'foo' => [
                    'driver' => 'baz',
                ],
                'baz' => [
                    'driver' => 'foo',
                ],
                'bar' => [],
            ],
            'driverAliases' => [
                'alias' => 'baz',
            ],
        ]);

        $this->assertSame([
            'foo' => [
                'driver' => 'baz',
            ],
        ], $config->getConnections('alias'));
    }

    public function testGetsConnectionsWithSpecificDriver(): void
    {
        $config = new QueueConfig([
            'connections' => [
                'foo' => [
                    'driver' => 'alias',
                ],
                'baz' => [
                    'driver' => 'baz',
                ],
                'bar' => [],
            ],
            'driverAliases' => [
                'alias' => 'baz',
            ],
        ]);

        $this->assertSame([
            'foo' => [
                'driver' => 'alias',
            ],
            'baz' => [
                'driver' => 'baz',
            ],
        ], $config->getConnections('baz'));
    }

    public function testGetsConnection(): void
    {
        $config = new QueueConfig([
            'connections' => [
                'foo' => [
                    'driver' => 'alias',
                ],
                'baz' => [
                    'driver' => 'bar',
                ],
                'bar' => [],
            ],
            'driverAliases' => [
                'alias' => 'baz',
            ],
        ]);

        $this->assertSame([
            'driver' => 'baz',
        ], $config->getConnection('foo'));
    }

    public function testGetsNonExistConnectionShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Queue connection with given name `foo` is not defined.');

        $config = new QueueConfig();
        $config->getConnection('foo');
    }

    public function testGetsConnectionWithoutDriverShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Driver for queue connection `foo` is not defined.');

        $config = new QueueConfig([
            'connections' => [
                'foo' => [],
            ],
        ]);

        $config->getConnection('foo');
    }

    public function testGetsConnectionWithWrongDriverValueTypeShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Driver for queue connection `foo` value must be a string');

        $config = new QueueConfig([
            'connections' => [
                'foo' => [
                    'driver' => []
                ],
            ],
        ]);

        $config->getConnection('foo');
    }

    public function testGetsConnectionWithWrongDriverAliasValueTypeShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Driver alias for queue connection `foo` value must be a string');

        $config = new QueueConfig([
            'connections' => [
                'foo' => [
                    'driver' => 'bar'
                ],
            ],
            'driverAliases' => [
                'bar' => []
            ]
        ]);

        $config->getConnection('foo');
    }

    public function testGetsRegistryHandlers(): void
    {
        $config = new QueueConfig([
            'registry' => [
                'handlers' => ['foo', 'bar'],
            ]
        ]);

        $this->assertSame(['foo', 'bar'], $config->getRegistryHandlers());
    }

    public function testGetsNotExistsRegistryHandlers(): void
    {
        $config = new QueueConfig();

        $this->assertSame([], $config->getRegistryHandlers());
    }

    public function testGetRegistrySerializers(): void
    {
        $config = new QueueConfig([
            'registry' => [
                'serializers' => ['foo' => 'some', 'bar' => 'other'],
            ]
        ]);

        $this->assertSame(['foo' => 'some', 'bar' => 'other'], $config->getRegistrySerializers());
    }

    public function testGetNotExistsRegistrySerializers(): void
    {
        $config = new QueueConfig();

        $this->assertSame([], $config->getRegistrySerializers());
    }

    /** @dataProvider defaultSerializerDataProvider */
    public function testGetDefaultSerializer($serializer, $expected): void
    {
        $config = new QueueConfig([
            'defaultSerializer' => $serializer
        ]);

        $this->assertEquals($expected, $config->getDefaultSerializer());
    }

    public function defaultSerializerDataProvider(): \Traversable
    {
        yield [null, new DefaultSerializer()];
        yield ['class-string', 'class-string'];
        yield [PhpSerializer::class, PhpSerializer::class];
        yield [new DefaultSerializer(), new DefaultSerializer()];
        yield [new Autowire(PhpSerializer::class), new Autowire(PhpSerializer::class)];
    }
}
