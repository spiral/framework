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

    public function testClose(): void
    {
        self::assertTrue($this->handler->close());
    }

    public function testDestroy(): void
    {
        $this->cache->shouldReceive('delete')->with('session:foo')->andReturn(true);

        self::assertTrue($this->handler->destroy('foo'));
    }

    public function testGc(): void
    {
        self::assertSame(0, $this->handler->gc(100));
    }

    public function testOpen(): void
    {
        self::assertTrue($this->handler->open('root', 'test'));
    }

    public function testRead(): void
    {
        $this->cache->shouldReceive('get')->with('session:foo')->andReturn('bar');

        self::assertSame('bar', $this->handler->read('foo'));
    }

    public function testReadExpired(): void
    {
        $this->cache->shouldReceive('get')->with('session:foo')->andReturn(null);

        self::assertSame('', $this->handler->read('foo'));
    }

    public function testWrite(): void
    {
        $this->cache->shouldReceive('set')->with('session:foo', 'bar', 86400)->andReturn(true);

        self::assertTrue($this->handler->write('foo', 'bar'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $storage = m::mock(CacheStorageProviderInterface::class);

        $storage->shouldReceive('storage')->once()->andReturn($this->cache = m::mock(CacheInterface::class));

        $this->handler = new CacheHandler(
            $storage,
        );
    }
}
