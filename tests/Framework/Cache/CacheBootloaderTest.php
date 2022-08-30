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
    /** @var \Spiral\App\TestApp */
    private $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->makeApp();
    }

    public function testBindings()
    {
        $this->assertInstanceOf(ArrayStorage::class, $this->app->get(CacheInterface::class));
        $this->assertInstanceOf(CacheManager::class, $this->app->get(CacheStorageProviderInterface::class));
    }

    public function testGetsStorageByAlias()
    {
        $manager = $this->app->get(CacheStorageProviderInterface::class);
        $this->assertInstanceOf(FileStorage::class, $manager->storage('user-data'));
    }
}
