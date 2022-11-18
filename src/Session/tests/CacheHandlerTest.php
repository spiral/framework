<?php

declare(strict_types=1);

namespace Spiral\Tests\Session;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Session\Handler\CacheHandler;
use Mockery as m;

final class CacheHandlerTest extends TestCase
{
    public function testClose(): void
    {
        $storage = m::mock(CacheStorageProviderInterface::class);

        $storage->shouldReceive('storage')->andReturn(m::mock(CacheInterface::class));

        $handler = new CacheHandler(
            $storage
        );

        $this->assertTrue($handler->close());
    }

    public function testDestroy(): void
    {
        $storage = m::mock(CacheStorageProviderInterface::class);

        $storage->shouldReceive('storage')->andReturn($cache = m::mock(CacheInterface::class));

        $handler = new CacheHandler(
            $storage
        );

        $cache->shouldReceive('delete')->with('1')->andReturn(false);

        $this->assertTrue($handler->destroy('1'));
    }

    public function testGc(): void
    {
        $storage = m::mock(CacheStorageProviderInterface::class);

        $storage->shouldReceive('storage')->andReturn(m::mock(CacheInterface::class));

        $handler = new CacheHandler(
            $storage
        );

        $this->assertSame(0, $handler->gc(100));
    }

    public function testOpen(): void
    {
        $storage = m::mock(CacheStorageProviderInterface::class);

        $storage->shouldReceive('storage')->andReturn(m::mock(CacheInterface::class));

        $handler = new CacheHandler(
            $storage
        );

        $this->assertTrue($handler->open('root', 'test'));
    }

    public function testRead(): void
    {
        $storage = m::mock(CacheStorageProviderInterface::class);

        $storage->shouldReceive('storage')->andReturn($cache = m::mock(CacheInterface::class));

        $handler = new CacheHandler(
            $storage
        );

        $cache->shouldReceive('get')->with('1')->andReturn('foo');

        $this->assertSame('foo', $handler->read('1'));
    }

    public function testReadExpired(): void
    {
        $storage = m::mock(CacheStorageProviderInterface::class);

        $storage->shouldReceive('storage')->andReturn($cache = m::mock(CacheInterface::class));

        $handler = new CacheHandler(
            $storage
        );

        $cache->shouldReceive('get')->with('1')->andReturn(null);

        $this->assertSame('', $handler->read('1'));
    }

    public function testWrite(): void
    {
        $storage = m::mock(CacheStorageProviderInterface::class);

        $storage->shouldReceive('storage')->andReturn($cache = m::mock(CacheInterface::class));

        $handler = new CacheHandler(
            $storage
        );

        $cache->shouldReceive('set')->with('session:1', 'foo', 86400)->andReturn(true);

        $this->assertTrue($handler->write('1', 'foo'));
    }
}
