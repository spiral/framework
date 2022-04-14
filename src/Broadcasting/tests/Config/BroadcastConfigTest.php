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
                    'bar-topic.{id}' => fn ($id) => $id,
                    'foo-topic' => fn () => 'foo',
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

    public function testNotDefinedDefaultKeyShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Default broadcast connection is not defined.');

        $config = new BroadcastConfig();

        $config->getDefaultConnection();
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
        $this->expectErrorMessage('Config for connection `foo` is not defined.');

        $this->config->getConnectionConfig('foo');
    }

    public function testConnectionWithoutDefinedDriverShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Driver for `memory` connection is not defined.');

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

    public function testFindsTopicCallback(): void
    {
        $params = [];
        $this->assertSame(
            'foo',
            call_user_func($this->config->findTopicCallback('foo-topic', $params))
        );

        $this->assertSame([0 => 'foo-topic'], $params);

        $params = [];
        $this->assertSame(
            5,
            call_user_func($this->config->findTopicCallback('bar-topic.5', $params), 5)
        );
        $this->assertSame([0 => 'bar-topic.5', 'id' =>'5', 1 => '5'], $params);


        $params = [];
        $this->assertNull(
            $this->config->findTopicCallback('baz-topic', $params)
        );
        $this->assertSame([], $params);
    }
}
