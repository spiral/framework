<?php

declare(strict_types=1);

namespace Framework\Auth;

use Spiral\Auth\Config\AuthConfig;
use Spiral\Auth\Exception\InvalidArgumentException;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TokenStorageProvider;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Tests\Framework\BaseTest;
use Mockery as m;

class TokenStorageProviderTest extends BaseTest
{
    public function testGetStorageByName(): void
    {
        $provider = new TokenStorageProvider(
            new AuthConfig([
                'storages' => [
                    'database' => $storage = m::mock(TokenStorageInterface::class),
                    'session' => 'test',
                ],
            ]), m::mock(FactoryInterface::class)
        );

        $this->assertSame($storage, $provider->getStorage('database'));
    }

    public function testGetDefaultStorage(): void
    {
        $provider = new TokenStorageProvider(
            new AuthConfig([
                'defaultStorage' => 'session',
                'storages' => [
                    'session' => $storage = m::mock(TokenStorageInterface::class),
                    'database' => 'test',
                ],
            ]), m::mock(FactoryInterface::class)
        );

        $this->assertSame($storage, $provider->getStorage());
    }

    public function testGetInvalidStorage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Token storage `%s` is not defined.', '123'));

        $provider = new TokenStorageProvider(
            new AuthConfig([
                'storages' => [
                    'session' => 'test1',
                    'database' => 'test2'
                ],
            ]), m::mock(FactoryInterface::class)
        );

        $provider->getStorage('123');
    }

    public function testGetInvalidDefaultStorage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Token storage `%s` is not defined.', 'invalid'));

        $provider = new TokenStorageProvider(
            new AuthConfig([
                'defaultStorage' => 'invalid',
                'storages' => [
                    'session' => 'test1',
                    'database' => 'test2'
                ],
            ]), m::mock(FactoryInterface::class)
        );

        $provider->getStorage();
    }

    function testGetAutowireStorage(): void
    {
        $storage = m::mock(TokenStorageInterface::class);

        $provider = new TokenStorageProvider(
            new AuthConfig([
                'storages' => [
                    'session' => new Autowire('...'),
                    'database' => 'test',
                ],
            ]), $factory = m::mock(FactoryInterface::class)
        );

        $factory->shouldReceive('make')->once()->with('...', [])->andReturn($storage);

        $this->assertSame($storage, $provider->getStorage('session'));
    }
}
