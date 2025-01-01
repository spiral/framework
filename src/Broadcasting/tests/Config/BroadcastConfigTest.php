<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Broadcasting\Config\BroadcastConfig;
use Spiral\Broadcasting\Exception\InvalidArgumentException;

final class BroadcastConfigTest extends TestCase
{
    private BroadcastConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new BroadcastConfig([
            'authorize' => [
                'path' => 'foo-path',
                'topics' => [
                    'bar-topic.{id}' => static fn (mixed $id): mixed => $id,
                    'foo-topic' => static fn (): string => 'foo',
                ],
            ],
            'default' => 'firebase',
            'aliases' => [
                'users-data' => 'firebase',
                'foo-data' => 'foo',
            ],

            'driverAliases' => [
                'log' => 'log-driver',
            ],

            'connections' => [
                'firebase' => [
                    'driver' => 'log',
                ],
                'null' => [
                    'driver' => 'null-driver',
                ],
                'memory' => [],
            ],
        ]);
    }


    public function testGetsDefaultConnection(): void
    {
        $this->assertSame(
            'firebase',
            $this->config->getDefaultConnection()
        );
    }

    public function testGetsConnectionConfigByName(): void
    {
        $this->assertSame(
            [
                'driver' => 'null-driver',
            ],
            $this->config->getConnectionConfig('null')
        );
    }

    public function testGetsConnectionWithAliasDriverShouldBeReplacedWithRealDriver(): void
    {
        $this->assertSame(
            [
                'driver' => 'log-driver',
            ],
            $this->config->getConnectionConfig('firebase')
        );
    }

    public function testNotDefinedConnectionShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Config for connection `foo` is not defined.');

        $this->config->getConnectionConfig('foo');
    }

    public function testConnectionWithoutDefinedDriverShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver for `memory` connection is not defined.');

        $this->config->getConnectionConfig('memory');
    }

    public function testGetAuthorizationPath(): void
    {
        $this->assertSame('foo-path', $this->config->getAuthorizationPath());
    }

    public function testNotDefinedAuthorizationPathShouldReturnNull(): void
    {
        $config = new BroadcastConfig();
        $this->assertNull($config->getAuthorizationPath());
    }

    public function testGetsTopics(): void
    {
        $this->assertSame(
            $this->config['authorize']['topics'],
            $this->config->getTopics()
        );
    }
}
