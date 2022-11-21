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

        $storage->shouldReceive('storage')->once()->andReturn($this->cache = m::mock(CacheInterface::class));

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
        $this->cache->shouldReceive('delete')->with('session:foo')->andReturn(true);

        $this->assertTrue($this->handler->destroy('foo'));
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
        $this->cache->shouldReceive('get')->with('session:foo')->andReturn('bar');

        $this->assertSame('bar', $this->handler->read('foo'));
    }

    public function testReadExpired(): void
    {
        $this->cache->shouldReceive('get')->with('session:foo')->andReturn('');

        $this->assertSame('', $this->handler->read('foo'));
    }

    public function testWrite(): void
    {
        $this->cache->shouldReceive('set')->with('session:foo', 'bar', 86400)->andReturn(true);

        $this->assertTrue($this->handler->write('foo', 'bar'));
    }
}
