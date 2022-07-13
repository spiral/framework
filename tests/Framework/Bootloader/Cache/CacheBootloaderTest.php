<?php

declare(strict_types=1);

namespace Framework\Bootloader\Cache;

use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\Bootloader\CacheBootloader;
use Spiral\Cache\CacheManager;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Cache\Storage\ArrayStorage;
use Spiral\Cache\Storage\FileStorage;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Tests\Framework\BaseTest;

final class CacheBootloaderTest extends BaseTest
{
    public function testBindings()
    {
        $this->assertContainerInstantiable(CacheInterface::class, ArrayStorage::class);
        $this->assertContainerBoundAsSingleton(CacheStorageProviderInterface::class, CacheManager::class);
    }

    public function testGetsStorageByAlias()
    {
        $manager = $this->getContainer()->get(CacheStorageProviderInterface::class);
        $this->assertInstanceOf(FileStorage::class, $manager->storage('user-data'));
    }

    public function testCacheInjector(): void
    {
        $this->assertTrue(
            $this->getContainer()->hasInjector(CacheInterface::class)
        );
    }

    public function testCacheConfigInjector(): void
    {
        $this->assertTrue(
            $this->getContainer()->hasInjector(CacheConfig::class)
        );
    }

    public function testRegisterTypeAlias(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(CacheConfig::CONFIG, ['typeAliases' => []]);

        $bootloader = new CacheBootloader($configs);
        $bootloader->registerTypeAlias('foo', 'bar');

        $this->assertSame(['bar' => 'foo'], $configs->getConfig(CacheConfig::CONFIG)['typeAliases']);
    }
}
