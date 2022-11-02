<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use Mockery as m;
use Spiral\Auth\Config\AuthConfig;
use Spiral\Auth\Exception\InvalidArgumentException;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TokenStorageProvider;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use PHPUnit\Framework\TestCase;

class TokenStorageProviderTest extends TestCase
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

    public function testGetAutowireStorage(): void
    {
        $storage = m::mock(TokenStorageInterface::class);

        $provider = new TokenStorageProvider(
            new AuthConfig([
                'storages' => [
                    'session' => new Autowire('some'),
                    'database' => 'test',
                ],
            ]), $factory = m::mock(FactoryInterface::class)
        );

        $factory->shouldReceive('make')->once()->with('some', [])->andReturn($storage);

        $this->assertSame($storage, $provider->getStorage('session'));
    }

    public function testGetStringStorage(): void
    {
        $storage = m::mock(TokenStorageInterface::class);

        $provider = new TokenStorageProvider(
            new AuthConfig([
                'defaultStorage' => 'session',
                'storages' => [
                    'session' => 'test1',
                    'database' => 'test2',
                ],
            ]), $factory = m::mock(FactoryInterface::class)
        );

        $factory->shouldReceive('make')->once()->with('test2')->andReturn($storage);

        $this->assertSame($storage, $provider->getStorage('database'));
    }

    public function testGetStorageTwice(): void
    {
        $storage = m::mock(TokenStorageInterface::class);

        $provider = new TokenStorageProvider(
            new AuthConfig([
                'defaultStorage' => 'session',
                'storages' => [
                    'database' => 'test',
                    'session' => 'test2'
                ],
            ]), $factory = m::mock(FactoryInterface::class)
        );

        $factory->shouldReceive('make')->once()->with('test')->andReturn($storage);

        $sameStorage = $provider->getStorage('database');

        $this->assertSame($sameStorage, $provider->getStorage('database'));
    }

    public function testGetDifferentStorage()
    {
        $provider = new TokenStorageProvider(
            new AuthConfig([
                'defaultStorage' => 'session',
                'storages' => [
                    'database' => 'test',
                    'session' => 'test2'
                ],
            ]), $factory = m::mock(FactoryInterface::class)
        );

        $factory->shouldReceive('make')
            ->once()
            ->with('test')
            ->andReturn(m::mock(TokenStorageInterface::class));

        $factory->shouldReceive('make')
            ->once()
            ->with('test2')
            ->andReturn(m::mock(TokenStorageInterface::class));

        $notSameStorage = $provider->getStorage('session');

        $this->assertNotSame($notSameStorage, $provider->getStorage('database'));
    }
}
