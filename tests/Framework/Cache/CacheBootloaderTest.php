<?php

declare(strict_types=1);

namespace Framework\Cache;

use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\CacheManager;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Cache\Storage\ArrayStorage;
use Spiral\Cache\Storage\FileStorage;
use Spiral\Tests\Framework\BaseTest;

final class CacheBootloaderTest extends BaseTest
{
    public function testBindings()
    {
        $this->assertContainerBoundAsSingleton(CacheInterface::class, ArrayStorage::class);
        $this->assertContainerBoundAsSingleton(CacheStorageProviderInterface::class, CacheManager::class);
    }

    public function testGetsStorageByAlias()
    {
        $manager = $this->getContainer()->get(CacheStorageProviderInterface::class);
        $this->assertInstanceOf(FileStorage::class, $manager->storage('user-data'));
    }
}
