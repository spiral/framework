<?php

declare(strict_types=1);

namespace Spiral\Tests\Cache;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\CacheManager;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Core\FactoryInterface;

final class CacheManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|FactoryInterface */
    private $factory;

    /** @var CacheManager */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $config = new CacheConfig([
            'default' => 'local',
            'aliases' => [
                'user-data' => 'local',
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

        $this->assertSame($storage, $this->manager->storage()->getStorage());
    }

    public function testGetStorageByName(): void
    {
        $storage = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('file-storage-class', [
            'type' => 'file-storage-class',
            'foo' => 'baz',
        ])->andReturn($storage);

        $this->assertSame($storage, $this->manager->storage('file')->getStorage());
    }

    public function testGetStorageWithStorageTypeAlias(): void
    {
        $storage = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('array-storage-class', [
            'type' => 'array-storage-class',
            'bar' => 'baz',
        ])->andReturn($storage);

        $this->assertSame($storage, $this->manager->storage('inMemory')->getStorage());
    }

    public function testGetStorageByAlias(): void
    {
        $storage = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('array-storage-class', [
            'type' => 'array-storage-class',
            'foo' => 'bar',
        ])->andReturn($storage);

        $this->assertSame($storage, $this->manager->storage('user-data')->getStorage());
    }

    public function testStorageShouldBeCreatedOnlyOnce(): void
    {
        $storage1 = m::mock(CacheInterface::class);
        $storage2 = m::mock(CacheInterface::class);

        $this->factory->shouldReceive('make')->once()->with('array-storage-class', [
            'type' => 'array-storage-class',
            'foo' => 'bar',
        ])->andReturn($storage1);

        $this->assertSame($storage1, $this->manager->storage()->getStorage());
        $this->assertSame($storage1, $this->manager->storage()->getStorage());

        $this->factory->shouldReceive('make')->once()->with('file-storage-class', [
            'type' => 'file-storage-class',
            'foo' => 'baz',
        ])->andReturn($storage2);

        $this->assertSame($storage2, $this->manager->storage('file')->getStorage());
        $this->assertSame($storage2, $this->manager->storage('file')->getStorage());
    }
}
