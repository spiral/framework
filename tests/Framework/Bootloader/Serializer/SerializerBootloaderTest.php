<?php

declare(strict_types=1);

namespace Framework\Bootloader\Serializer;

use Spiral\Serializer\Config\SerializerConfig;
use Spiral\Serializer\SerializerInterface;
use Spiral\Serializer\SerializerManager;
use Spiral\Serializer\SerializerRegistry;
use Spiral\Serializer\SerializerRegistryInterface;
use Spiral\Tests\Framework\BaseTest;

final class SerializerBootloaderTest extends BaseTest
{
    public const ENV = [
        'DEFAULT_SERIALIZER_FORMAT' => 'test',
    ];

    public function testSerializerManagerBinding(): void
    {
        $this->assertContainerBoundAsSingleton(SerializerManager::class, SerializerManager::class);
    }

    public function testSerializerRegistryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(SerializerRegistryInterface::class, SerializerRegistry::class);
    }

    public function testSerializerInterface(): void
    {
        $this->assertContainerBoundAsSingleton(SerializerInterface::class, SerializerManager::class);
    }

    public function testConfig(): void
    {
        $this->assertSame(
            'test',
            $this->getConfig(SerializerConfig::CONFIG)['default']
        );
    }
}
