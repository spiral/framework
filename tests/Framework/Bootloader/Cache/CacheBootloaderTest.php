<?php

declare(strict_types=1);

namespace Framework\Bootloader\Cache;

use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\Bootloader\CacheBootloader;
use Spiral\Cache\CacheManager;
use Spiral\Cache\CacheRepository;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Cache\CacheStorageRegistryInterface;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Cache\Storage\ArrayStorage;
use Spiral\Cache\Storage\FileStorage;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class CacheBootloaderTest extends BaseTestCase
{
    public function testBindings(): void
    {
        $this->assertContainerInstantiable(CacheInterface::class, CacheRepository::class);
        $this->assertContainerBoundAsSingleton(CacheStorageProviderInterface::class, CacheManager::class);
        $this->assertContainerBoundAsSingleton(CacheStorageRegistryInterface::class, CacheManager::class);

        $repository = $this->getContainer()->get(CacheInterface::class);
        self::assertInstanceOf(ArrayStorage::class, $repository->getStorage());

        $provider = $this->getContainer()->get(CacheStorageProviderInterface::class);
        self::assertInstanceOf(CacheManager::class, $provider);
        $registry = $this->getContainer()->get(CacheStorageRegistryInterface::class);
        self::assertInstanceOf(CacheManager::class, $registry);
    }

    public function testGetsStorageByAlias(): void
    {
        $manager = $this->getContainer()->get(CacheStorageProviderInterface::class);
        $repository = $manager->storage('user-data');

        self::assertInstanceOf(FileStorage::class, $repository->getStorage());
    }

    public function testCacheInjector(): void
    {
        self::assertTrue($this->getContainer()->hasInjector(CacheInterface::class));
    }

    public function testCacheConfigInjector(): void
    {
        self::assertTrue($this->getContainer()->hasInjector(CacheConfig::class));
    }

    public function testRegisterTypeAlias(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(CacheConfig::CONFIG, ['typeAliases' => []]);

        $bootloader = new CacheBootloader($configs);
        $bootloader->registerTypeAlias('foo', 'bar');

        self::assertSame(['bar' => 'foo'], $configs->getConfig(CacheConfig::CONFIG)['typeAliases']);
    }
}
