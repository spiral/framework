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
    private CacheHandler $handler;
    private m\MockInterface|CacheInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $storage = m::mock(CacheStorageProviderInterface::class);

        $storage->shouldReceive('storage')->once()->andReturn($cache = m::mock(CacheInterface::class));

        $this->cache = $cache;

        $this->handler = new CacheHandler(
            $storage
        );
    }

    public function testClose(): void
    {
        $this->assertTrue($this->handler->close());
    }

    public function testDestroy(): void
    {
        $this->cache->shouldReceive('delete')->with('1')->andReturn(false);

        $this->assertTrue($this->handler->destroy('1'));
    }

    public function testGc(): void
    {
        $this->assertSame(0, $this->handler->gc(100));
    }

    public function testOpen(): void
    {
        $this->assertTrue($this->handler->open('root', 'test'));
    }

    public function testRead(): void
    {
        $this->cache->shouldReceive('get')->with('session-id')->andReturn('foo');

        $this->assertSame('foo', $this->handler->read('session-id'));
    }

    public function testReadExpired(): void
    {
        $this->cache->shouldReceive('get')->with('session-id')->andReturn(null);

        $this->assertSame('', $this->handler->read('session-id'));
    }

    public function testWrite(): void
    {
        $this->cache->shouldReceive('set')->with('session:1', 'foo', 86400)->andReturn(true);

        $this->assertTrue($this->handler->write('1', 'foo'));
    }
}
