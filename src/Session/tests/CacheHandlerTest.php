<?php

declare(strict_types=1);

namespace Spiral\Tests\Session;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Session\Handler\CacheHandler;

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

    public function testCreateSid(): void
    {
        $id = $this->handler->create_sid();

        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $id);
        self::assertNotSame($id, $this->handler->create_sid());
    }

    public function testValidateId(): void
    {
        $this->cache->shouldReceive('has')->with('session:foo')->andReturn(true);
        $this->cache->shouldReceive('has')->with('session:bar')->andReturn(false);

        self::assertTrue($this->handler->validateId('foo'));
        self::assertFalse($this->handler->validateId('bar'));
    }

    public function testUpdateTimestamp(): void
    {
        $this->cache->shouldReceive('set')->with('session:foo', 'bar', 86400)->once()->andReturn(true);

        self::assertTrue($this->handler->updateTimestamp('foo', 'bar'));
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
