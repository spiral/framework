<?php

declare(strict_types=1);

namespace Spiral\Tests\Cache\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Cache\Exception\InvalidArgumentException;

final class CacheConfigTest extends TestCase
{
    /** @var CacheConfig */
    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new CacheConfig([
            'default' => 'array',

            'aliases' => [
                'users-data' => 'local',
                'foo-data' => 'foo',
            ],

            'typeAliases' => [
                'array' => 'array-storage',
            ],

            'storages' => [
                'local' => [
                    'type' => 'array',
                ],
                'filesystem' => [
                    'type' => 'file-storage',
                ],
                'memory' => [],
            ],
        ]);
    }


    public function testGetdDefaultDriver(): void
    {
        $this->assertSame(
            'array',
            $this->config->getDefaultStorage()
        );
    }

    public function testNotDefinedDefaultKeyShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Default cache storage is not defined.');

        $config = new CacheConfig();

        $config->getDefaultStorage();
    }

    public function testGetsStorageConfigByStorageName(): void
    {
        $this->assertSame(
            [
                'type' => 'file-storage',
            ],
            $this->config->getStorageConfig('filesystem')
        );
    }

    public function testGetsStorageWithAliasTypeShouldBeReplacedWithRealType(): void
    {
        $this->assertSame(
            [
                'type' => 'array-storage',
            ],
            $this->config->getStorageConfig('local')
        );
    }

    public function testNotDefinedStorageShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Config for storage `foo` is not defined.');

        $this->config->getStorageConfig('foo');
    }

    public function testStorageWithoutDefinedTypeShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Storage type for `memory` is not defined.');

        $this->config->getStorageConfig('memory');
    }
}
