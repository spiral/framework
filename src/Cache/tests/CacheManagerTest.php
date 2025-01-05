<?php

declare(strict_types=1);

namespace Spiral\Tests\Cache;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\CacheManager;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Cache\Storage\ArrayStorage;
use Spiral\Core\FactoryInterface;

final class CacheManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|FactoryInterface */
    private $factory;

    private CacheManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $config = new CacheConfig([
            'default' => 'local',
            'aliases' => [
                'user-data' => 'local',
                'blog-data' => ['storage' => 'file', 'prefix' => 'blog_'],
                'news-data' => ['storage' => 'file', 'prefix' => 'news_'],
                'store-data' => ['storage' => 'file', 'prefix' => ''],
                'order-data' => ['storage' => 'file', 'prefix' => null],
                'delivery-data' => ['storage' => 'file']
            ],
            'typeAliases' => [
                'array' => 'array-storage-class',
            ],
            'storages' => [
                'local' => [
                    'type' => 'array-storage-class',
                    'foo' => 'bar',
                ],
                'file' => [
                    'type' => 'file-storage-class',
                    'foo' => 'baz',
                ],
                'inMemory' => [
                    'type' => 'array',
                    'bar' => 'baz',
                ],
            ],
        ]);

        $this->factory = m::mock(FactoryInterface::class);
        $this->manager = new CacheManager($config, $this->factory);
    }

    public function testGetDefaultStorage(): void
    {
        $storage = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('array-storage-class', [
            'type' => 'array-storage-class',
            'foo' => 'bar',
        ])->andReturn($storage);

        self::assertSame($storage, $this->manager->storage()->getStorage());
    }

    public function testGetStorageByName(): void
    {
        $storage = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('file-storage-class', [
            'type' => 'file-storage-class',
            'foo' => 'baz',
        ])->andReturn($storage);

        self::assertSame($storage, $this->manager->storage('file')->getStorage());
    }

    public function testGetStorageWithStorageTypeAlias(): void
    {
        $storage = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('array-storage-class', [
            'type' => 'array-storage-class',
            'bar' => 'baz',
        ])->andReturn($storage);

        self::assertSame($storage, $this->manager->storage('inMemory')->getStorage());
    }

    public function testGetStorageByAlias(): void
    {
        $storage = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('array-storage-class', [
            'type' => 'array-storage-class',
            'foo' => 'bar',
        ])->andReturn($storage);

        self::assertSame($storage, $this->manager->storage('user-data')->getStorage());
    }

    public function testStorageShouldBeCreatedOnlyOnce(): void
    {
        $storage1 = m::mock(CacheInterface::class);
        $storage2 = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('array-storage-class', [
            'type' => 'array-storage-class',
            'foo' => 'bar',
        ])->andReturn($storage1);

        self::assertSame($storage1, $this->manager->storage()->getStorage());
        self::assertSame($storage1, $this->manager->storage()->getStorage());

        $this->factory->shouldReceive('make')->once()->with('file-storage-class', [
            'type' => 'file-storage-class',
            'foo' => 'baz',
        ])->andReturn($storage2);

        self::assertSame($storage2, $this->manager->storage('file')->getStorage());
        self::assertSame($storage2, $this->manager->storage('file')->getStorage());
    }

    public function testStorageShouldBeCreatedOnlyOnceWithDifferentPrefixes(): void
    {
        $storage = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('file-storage-class', [
            'type' => 'file-storage-class',
            'foo' => 'baz',
        ])->andReturn($storage);

        $blog = $this->manager->storage('blog-data');
        $news = $this->manager->storage('news-data');

        self::assertSame($storage, $blog->getStorage());
        self::assertSame($storage, $news->getStorage());

        self::assertSame('blog_', (new \ReflectionProperty($blog, 'prefix'))->getValue($blog));
        self::assertSame('news_', (new \ReflectionProperty($news, 'prefix'))->getValue($news));
    }

    #[DataProvider('prefixesDataProvider')]
    public function testGetStorageByAliasWithPrefix(string $alias, ?string $expectedPrefix): void
    {
        $storage = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('file-storage-class', [
            'type' => 'file-storage-class',
            'foo' => 'baz',
        ])->andReturn($storage);

        $repo = $this->manager->storage($alias);

        self::assertSame($storage, $repo->getStorage());
        self::assertSame($expectedPrefix, (new \ReflectionProperty($repo, 'prefix'))->getValue($repo));
    }

    public function testCacheRepositoryWithoutEventDispatcher(): void
    {
        $this->factory->shouldReceive('make')->once()->with('array-storage-class', [
            'type' => 'array-storage-class',
            'foo' => 'bar',
        ])->andReturn(m::mock(CacheInterface::class));

        $manager = new CacheManager(new CacheConfig([
            'storages' => [
                'test' => [
                    'type' => 'array-storage-class',
                    'foo' => 'bar',
                ],
            ],
        ]), $this->factory);
        $repository = $manager->storage('test');

        self::assertNull((new \ReflectionProperty($repository, 'dispatcher'))->getValue($repository));
    }

    public function testCacheRepositoryWithEventDispatcher(): void
    {
        $dispatcher = m::mock(EventDispatcherInterface::class);

        $this->factory->shouldReceive('make')->once()->with('array-storage-class', [
            'type' => 'array-storage-class',
            'foo' => 'bar',
        ])->andReturn(m::mock(CacheInterface::class));

        $manager = new CacheManager(new CacheConfig([
            'storages' => [
                'test' => [
                    'type' => 'array-storage-class',
                    'foo' => 'bar',
                ],
            ],
        ]), $this->factory, $dispatcher);
        $repository = $manager->storage('test');

        self::assertSame($dispatcher, (new \ReflectionProperty($repository, 'dispatcher'))->getValue($repository));
    }

    public static function prefixesDataProvider(): \Traversable
    {
        yield ['blog-data', 'blog_'];
        yield ['store-data', null];
        yield ['order-data', null];
        yield ['delivery-data', null];
    }

    public function testRegister(): void
    {
        $uniq = \uniqid();
        $cache = new ArrayStorage();
        $cache->set('uniq', $uniq);
        $name = 'brandNewCache';
        self::assertArrayNotHasKey($name, $this->manager->getCacheStorages());

        $this->manager->register($name, $cache);
        self::assertArrayHasKey($name, $this->manager->getCacheStorages());

        $repo = $this->manager->storage($name);
        self::assertSame($uniq, $repo->get('uniq'));
    }

    public function testMany(): void
    {
        $cache1 = new ArrayStorage();
        $name1 = 'brandNewCache';
        $cache2 = new ArrayStorage();
        $name2 = 'brandNewCache2';

        $this->manager->register($name1, $cache1);
        $this->manager->register($name2, $cache2);
        self::assertEquals([
            $name1 => $cache1,
            $name2 => $cache2,
        ], $this->manager->getCacheStorages());
    }
}
