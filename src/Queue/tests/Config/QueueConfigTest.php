<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Core\Container\Autowire;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Tests\Queue\TestCase;

final class QueueConfigTest extends TestCase
{
    public static function defaultSerializerDataProvider(): \Generator
    {
        yield [[], null];
        yield [['defaultSerializer' => null], null];
        yield [['defaultSerializer' => 'json'], 'json'];
        yield [['defaultSerializer' => JsonSerializer::class], JsonSerializer::class];
        yield [['defaultSerializer' => new JsonSerializer()], new JsonSerializer()];
        yield [['defaultSerializer' => new Autowire(JsonSerializer::class)], new Autowire(JsonSerializer::class)];
    }

    public function testGetsAliases(): void
    {
        $config = new QueueConfig([
            'aliases' => ['foo', 'bar'],
        ]);

        self::assertSame(['foo', 'bar'], $config->getAliases());
    }

    public function testConsumeInterceptors(): void
    {
        $config = new QueueConfig([
            'interceptors' => [
                'consume' => ['foo', 'bar'],
            ],
        ]);

        self::assertSame(['foo', 'bar'], $config->getConsumeInterceptors());
    }

    public function testPushInterceptors(): void
    {
        $config = new QueueConfig([
            'interceptors' => [
                'push' => ['foo', 'bar'],
            ],
        ]);

        self::assertSame(['foo', 'bar'], $config->getPushInterceptors());
    }

    public function testGetNotExistsAliases(): void
    {
        $config = new QueueConfig();

        self::assertSame([], $config->getAliases());
    }

    public function testGetsDefaultDriver(): void
    {
        $config = new QueueConfig([
            'default' => 'foo',
        ]);
        self::assertSame('foo', $config->getDefaultDriver());
    }

    public function testGetsNonStringDefaultDriverShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Default queue connection config value must be a string');

        $config = new QueueConfig(['default' => ['foo']]);

        $config->getDefaultDriver();
    }

    public function testGetsDriverAliases(): void
    {
        $config = new QueueConfig([
            'driverAliases' => ['foo', 'bar'],
        ]);

        self::assertSame(['foo', 'bar'], $config->getDriverAliases());
    }

    public function testGetNotExistsDriverAliases(): void
    {
        $config = new QueueConfig();

        self::assertSame([], $config->getDriverAliases());
    }

    public function testGetsConnectionsWithoutDriver(): void
    {
        $config = new QueueConfig([
            'connections' => ['foo', 'bar'],
        ]);

        self::assertSame(['foo', 'bar'], $config->getConnections());
    }

    public function testGetsNotExistsConnections(): void
    {
        $config = new QueueConfig();

        self::assertSame([], $config->getConnections());
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

        self::assertSame([
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

        self::assertSame([
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

        self::assertSame([
            'driver' => 'baz',
        ], $config->getConnection('foo'));
    }

    public function testGetsNonExistConnectionShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Queue connection with given name `foo` is not defined.');

        $config = new QueueConfig();
        $config->getConnection('foo');
    }

    public function testGetsConnectionWithoutDriverShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver for queue connection `foo` is not defined.');

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
        $this->expectExceptionMessage('Driver for queue connection `foo` value must be a string');

        $config = new QueueConfig([
            'connections' => [
                'foo' => [
                    'driver' => [],
                ],
            ],
        ]);

        $config->getConnection('foo');
    }

    public function testGetsConnectionWithWrongDriverAliasValueTypeShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver alias for queue connection `foo` value must be a string');

        $config = new QueueConfig([
            'connections' => [
                'foo' => [
                    'driver' => 'bar',
                ],
            ],
            'driverAliases' => [
                'bar' => [],
            ],
        ]);

        $config->getConnection('foo');
    }

    public function testGetsRegistryHandlers(): void
    {
        $config = new QueueConfig([
            'registry' => [
                'handlers' => ['foo', 'bar'],
            ],
        ]);

        self::assertSame(['foo', 'bar'], $config->getRegistryHandlers());
    }

    public function testGetsNotExistsRegistryHandlers(): void
    {
        $config = new QueueConfig();

        self::assertSame([], $config->getRegistryHandlers());
    }

    public function testGetRegistrySerializers(): void
    {
        $config = new QueueConfig([
            'registry' => [
                'serializers' => ['foo' => 'some', 'bar' => 'other'],
            ],
        ]);

        self::assertSame(['foo' => 'some', 'bar' => 'other'], $config->getRegistrySerializers());
    }

    public function testGetNotExistsRegistrySerializers(): void
    {
        $config = new QueueConfig();

        self::assertSame([], $config->getRegistrySerializers());
    }

    #[DataProvider('defaultSerializerDataProvider')]
    public function testGetDefaultSerializer(array $config, mixed $expected): void
    {
        $config = new QueueConfig($config);

        self::assertEquals($expected, $config->getDefaultSerializer());
    }
}
