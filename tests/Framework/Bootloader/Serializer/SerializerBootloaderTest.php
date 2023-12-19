<?php

declare(strict_types=1);

namespace Framework\Bootloader\Serializer;

use Spiral\Serializer\Config\SerializerConfig;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\Serializer\ProtoSerializer;
use Spiral\Serializer\SerializerInterface;
use Spiral\Serializer\SerializerManager;
use Spiral\Serializer\SerializerRegistry;
use Spiral\Serializer\SerializerRegistryInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class SerializerBootloaderTest extends BaseTestCase
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

    public function testDefaultConfig(): void
    {
        $config = $this->getConfig(SerializerConfig::CONFIG);

        $this->assertEquals([
            'default' => 'test',
            'serializers' => [
                'json' => new JsonSerializer(),
                'serializer' => new PhpSerializer(),
                'proto' => new ProtoSerializer(),
            ],
        ], $config);
    }

    public function testConfig(): void
    {
        $this->assertSame(
            'test',
            $this->getConfig(SerializerConfig::CONFIG)['default']
        );
    }
}
