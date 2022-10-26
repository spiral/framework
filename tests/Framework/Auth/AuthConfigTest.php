<?php

namespace Framework\Auth;

use Spiral\Auth\Config\AuthConfig;
use Spiral\Auth\TokenStorageInterface as SessionTokenStorageInterface;
use Spiral\Tests\Framework\BaseTest;
use Mockery as m;

class AuthConfigTest extends BaseTest
{
    public function testGetStorage()
    {
        $config = new AuthConfig([
            'storages' => [
                'session' => $session = m::mock(SessionTokenStorageInterface::class),
                'database' => 'test'
            ]
        ]);

        $this->assertSame($session, $config->getStorage('session'));
    }

    public function testGetDefaultStorage()
    {
        $config = new AuthConfig([
            'defaultStorage' => 'test'
        ]);

        $this->assertSame('test', $config->getDefaultStorage());
    }
}
