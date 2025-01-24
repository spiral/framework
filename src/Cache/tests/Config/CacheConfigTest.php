<?php

declare(strict_types=1);

namespace Spiral\Tests\Cache\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Cache\Exception\InvalidArgumentException;

final class CacheConfigTest extends TestCase
{
    private CacheConfig $config;

    public function testGetdDefaultDriver(): void
    {
        self::assertSame('array', $this->config->getDefaultStorage());
    }

    public function testGetsStorageConfigByStorageName(): void
    {
        self::assertSame([
            'type' => 'file-storage',
        ], $this->config->getStorageConfig('filesystem'));
    }

    public function testGetsStorageWithAliasTypeShouldBeReplacedWithRealType(): void
    {
        self::assertSame([
            'type' => 'array-storage',
        ], $this->config->getStorageConfig('local'));
    }

    public function testNotDefinedStorageShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Config for storage `foo` is not defined.');

        $this->config->getStorageConfig('foo');
    }

    public function testStorageWithoutDefinedTypeShouldThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage type for `memory` is not defined.');

        $this->config->getStorageConfig('memory');
    }

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
}
