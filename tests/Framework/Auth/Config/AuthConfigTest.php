<?php

namespace Framework\Auth\Config;

use Spiral\Auth\Config\AuthConfig;
use Spiral\Auth\TokenStorageInterface as SessionTokenStorageInterface;
use Spiral\Tests\Framework\BaseTestCase;
use Mockery as m;

class AuthConfigTest extends BaseTestCase
{
    public function testGetStorage(): void
    {
        $config = new AuthConfig([
            'storages' => [
                'session' => $session = m::mock(SessionTokenStorageInterface::class),
                'database' => 'test',
            ],
        ]);

        self::assertSame($session, $config->getStorage('session'));
    }

    public function testGetDefaultStorage(): void
    {
        $config = new AuthConfig([
            'defaultStorage' => 'test',
        ]);

        self::assertSame('test', $config->getDefaultStorage());
    }

    public function testGetEmptyDefaultStorage(): void
    {
        $config = new AuthConfig([]);

        self::assertSame('session', $config->getDefaultStorage());
    }

    public function testGetDefaultTransport(): void
    {
        $config = new AuthConfig([
            'defaultTransport' => 'header',
        ]);

        self::assertSame('header', $config->getDefaultTransport());
    }

    public function testGetEmptyDefaultTransport(): void
    {
        $config = new AuthConfig([]);

        self::assertSame('cookie', $config->getDefaultTransport());
    }

    public function testGetTransports(): void
    {
        $config = new AuthConfig([
            'transports' => [],
        ]);

        self::assertSame([], $config->getTransports());
    }

    public function testGetEmptyTransports(): void
    {
        $config = new AuthConfig([]);

        self::assertSame([], $config->getTransports());
    }
}
