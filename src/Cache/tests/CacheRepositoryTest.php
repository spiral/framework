<?php

declare(strict_types=1);

namespace Spiral\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\CacheRepository;
use Spiral\Cache\Event\CacheHit;
use Spiral\Cache\Event\CacheMissed;
use Spiral\Cache\Event\KeyDeleted;
use Spiral\Cache\Event\KeyWritten;
use Spiral\Cache\Storage\ArrayStorage;

final class CacheRepositoryTest extends TestCase
{
    public const DEFAULT_TTL = 50;

    public function testKeyWrittenAndCacheHitEventsShouldBeDispatched(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyWritten('test', []),
                new CacheHit('test', [])
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->set('test', []);
        $repository->get('test');
    }

    public function testKeyWrittenAndCacheHitEventsShouldBeDispatchedInMultiple(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(4))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyWritten('test', []),
                new KeyWritten('test2', []),
                new CacheHit('test', []),
                new CacheHit('test2', [])
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->setMultiple(['test' => [], 'test2' => []]);
        $repository->getMultiple(['test', 'test2']);
    }

    public function testCacheMissedEventShouldBeDispatched(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(new CacheMissed('test'));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->get('test');
    }

    public function testCacheMissedEventShouldBeDispatchedInMultiple(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with($this->logicalOr(
                new CacheMissed('test'),
                new CacheMissed('test2')
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->getMultiple(['test', 'test2']);
    }

    public function testKeyDeletedEventShouldBeDispatched(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyWritten('test', []),
                new KeyDeleted('test')
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->set('test', []);
        $repository->delete('test');
    }

    public function testKeyDeletedEventShouldBeDispatchedInMultiple(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(4))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyWritten('test', []),
                new KeyDeleted('test'),
                new KeyWritten('test2', []),
                new KeyDeleted('test2')
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->setMultiple(['test' => [], 'test2' => []]);
        $repository->deleteMultiple(['test', 'test2']);
    }

    /**
     * @dataProvider keysDataProvider
     */
    public function testGet(string $expectedKey, ?string $prefix = null): void
    {
        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects($this->once())
            ->method('get')
            ->with($expectedKey)
            ->willReturn(null);

        $repository = new CacheRepository(storage: $storage, prefix: $prefix);

        $repository->get('data');
    }

    /**
     * @dataProvider keysDataProvider
     */
    public function testSet(string $expectedKey, ?string $prefix = null): void
    {
        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects($this->once())
            ->method('set')
            ->with($expectedKey, 'foo')
            ->willReturn(true);

        $repository = new CacheRepository(storage: $storage, prefix: $prefix);

        $repository->set('data', 'foo');
    }

    /**
     * @dataProvider keysDataProvider
     */
    public function testDelete(string $expectedKey, ?string $prefix = null): void
    {
        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects($this->once())
            ->method('delete')
            ->with($expectedKey)
            ->willReturn(true);

        $repository = new CacheRepository(storage: $storage, prefix: $prefix);

        $repository->delete('data');
    }

    /**
     * @dataProvider keysDataProvider
     */
    public function testGetMultiple(string $expectedKey, ?string $prefix = null): void
    {
        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects($this->once())
            ->method('get')
            ->with($expectedKey)
            ->willReturn(null);

        $repository = new CacheRepository(storage: $storage, prefix: $prefix);

        $repository->getMultiple(['data']);
    }

    /**
     * @dataProvider keysDataProvider
     */
    public function testSetMultiple(string $expectedKey, ?string $prefix = null): void
    {
        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects($this->once())
            ->method('set')
            ->with($expectedKey, 'foo')
            ->willReturn(true);

        $repository = new CacheRepository(storage: $storage, prefix: $prefix);

        $repository->setMultiple(['data' => 'foo']);
    }

    /**
     * @dataProvider keysDataProvider
     */
    public function testDeleteMultiple(string $expectedKey, ?string $prefix = null): void
    {
        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects($this->once())
            ->method('delete')
            ->with($expectedKey)
            ->willReturn(true);

        $repository = new CacheRepository(storage: $storage, prefix: $prefix);

        $repository->deleteMultiple(['data']);
    }

    /**
     * @dataProvider keysDataProvider
     */
    public function testHas(string $expectedKey, ?string $prefix = null): void
    {
        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects($this->once())
            ->method('has')
            ->with($expectedKey)
            ->willReturn(true);

        $repository = new CacheRepository(storage: $storage, prefix: $prefix);

        $repository->has('data');
    }

    public function keysDataProvider(): \Traversable
    {
        yield ['data'];
        yield ['data', ''];
        yield ['data', null];
        yield ['user_data', 'user_'];
    }
}
