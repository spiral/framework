<?php

namespace Framework\Auth;

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
                'database' => 'test'
            ]
        ]);

        $this->assertSame($session, $config->getStorage('session'));
    }

    public function testGetDefaultStorage(): void
    {
        $config = new AuthConfig([
            'defaultStorage' => 'test'
        ]);

        $this->assertSame('test', $config->getDefaultStorage());
    }

    public function testGetEmptyDefaultStorage(): void
    {
        $config = new AuthConfig([]);

        $this->assertSame('session', $config->getDefaultStorage());
    }

    public function testGetDefaultTransport(): void
    {
        $config = new AuthConfig([
            'defaultTransport' => 'header'
        ]);

        $this->assertSame('header', $config->getDefaultTransport());
    }

    public function testGetEmptyDefaultTransport(): void
    {
        $config = new AuthConfig([]);

        $this->assertSame('cookie', $config->getDefaultTransport());
    }

    public function testGetTransports(): void
    {
        $config = new AuthConfig([
            'transports' => []
        ]);

        $this->assertSame([], $config->getTransports());
    }

    public function testGetEmptyTransports(): void
    {
        $config = new AuthConfig([]);

        $this->assertSame([], $config->getTransports());
    }
}
