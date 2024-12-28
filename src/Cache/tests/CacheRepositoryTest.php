<?php

declare(strict_types=1);

namespace Spiral\Tests\Cache;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\CacheRepository;
use Spiral\Cache\Event\CacheHit;
use Spiral\Cache\Event\CacheMissed;
use Spiral\Cache\Event\CacheRetrieving;
use Spiral\Cache\Event\KeyDeleted;
use Spiral\Cache\Event\KeyDeleteFailed;
use Spiral\Cache\Event\KeyDeleting;
use Spiral\Cache\Event\KeyWriteFailed;
use Spiral\Cache\Event\KeyWriting;
use Spiral\Cache\Event\KeyWritten;
use Spiral\Cache\Storage\ArrayStorage;

final class CacheRepositoryTest extends TestCase
{
    public const DEFAULT_TTL = 50;

    public function testKeyWriteAndCacheHitEventsShouldBeDispatched(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(4))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyWriting('test', []),
                new KeyWritten('test', []),
                new CacheRetrieving('test'),
                new CacheHit('test', []),
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->set('test', []);
        $repository->get('test');
    }

    public function testKeyWriteAndCacheHitEventsShouldBeDispatchedInMultiple(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(8))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyWriting('test', []),
                new KeyWritten('test', []),
                new KeyWriting('test2', []),
                new KeyWritten('test2', []),
                new CacheRetrieving('test'),
                new CacheHit('test', []),
                new CacheRetrieving('test2'),
                new CacheHit('test2', [])
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->setMultiple(['test' => [], 'test2' => []]);
        $repository->getMultiple(['test', 'test2']);
    }

    public function testKeyWriteFailedEventShouldBeDispatched(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyWriting('test', []),
                new KeyWriteFailed('test', []),
            ));

        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects(self::once())
            ->method('set')
            ->with('test', [])
            ->willReturn(false);

        $repository = new CacheRepository($storage, $dispatcher);

        $repository->set('test', []);
    }

    public function testKeyWriteFailedEventShouldBeDispatchedInMultiple(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(4))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyWriting('test', []),
                new KeyWriteFailed('test', []),
                new KeyWriting('test2', []),
                new KeyWriteFailed('test2', []),
            ));

        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects(self::exactly(2))
            ->method('set')
            ->willReturn(false);

        $repository = new CacheRepository($storage, $dispatcher);

        $repository->setMultiple(['test' => [], 'test2' => []]);
    }

    public function testCacheMissedEventShouldBeDispatched(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with($this->logicalOr(
                new CacheRetrieving('test'),
                new CacheMissed('test'),
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->get('test');
    }

    public function testCacheMissedEventShouldBeDispatchedInMultiple(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(4))
            ->method('dispatch')
            ->with($this->logicalOr(
                new CacheRetrieving('test'),
                new CacheMissed('test'),
                new CacheRetrieving('test2'),
                new CacheMissed('test2')
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->getMultiple(['test', 'test2']);
    }

    public function testKeyDeleteEventsShouldBeDispatched(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(4))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyWriting('test', []),
                new KeyWritten('test', []),
                new KeyDeleting('test'),
                new KeyDeleted('test')
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->set('test', []);
        $repository->delete('test');
    }

    public function testKeyDeleteEventsShouldBeDispatchedInMultiple(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(8))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyWriting('test', []),
                new KeyWritten('test', []),
                new KeyWriting('test2', []),
                new KeyWritten('test2', []),
                new KeyDeleting('test'),
                new KeyDeleted('test'),
                new KeyDeleting('test2'),
                new KeyDeleted('test2')
            ));

        $repository = new CacheRepository(new ArrayStorage(self::DEFAULT_TTL), $dispatcher);

        $repository->setMultiple(['test' => [], 'test2' => []]);
        $repository->deleteMultiple(['test', 'test2']);
    }

    public function testKeyDeleteFailedShouldBeDispatched(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyDeleting('test'),
                new KeyDeleteFailed('test')
            ));

        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects(self::once())
            ->method('delete')
            ->with('test')
            ->willReturn(false);

        $repository = new CacheRepository($storage, $dispatcher);

        $repository->delete('test');
    }

    public function testKeyDeleteFailedShouldBeDispatchedInMultiple(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(4))
            ->method('dispatch')
            ->with($this->logicalOr(
                new KeyDeleting('test'),
                new KeyDeleteFailed('test'),
                new KeyDeleting('test2'),
                new KeyDeleteFailed('test2')
            ));

        $storage = $this->createMock(CacheInterface::class);
        $storage
            ->expects(self::exactly(2))
            ->method('delete')
            ->willReturn(false);

        $repository = new CacheRepository($storage, $dispatcher);

        $repository->deleteMultiple(['test', 'test2']);
    }

    #[DataProvider('keysDataProvider')]
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

    #[DataProvider('keysDataProvider')]
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

    #[DataProvider('keysDataProvider')]
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

    #[DataProvider('keysDataProvider')]
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

    #[DataProvider('keysDataProvider')]
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

    #[DataProvider('keysDataProvider')]
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

    #[DataProvider('keysDataProvider')]
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

    public static function keysDataProvider(): \Traversable
    {
        yield ['data'];
        yield ['data', ''];
        yield ['data', null];
        yield ['user_data', 'user_'];
    }
}
