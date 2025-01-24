<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Bootloader;

use PHPUnit\Framework\TestCase;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Serializer\Bootloader\SerializerBootloader;
use Spiral\Serializer\Config\SerializerConfig;
use Spiral\Serializer\Exception\SerializerNotFoundException;
use Spiral\Serializer\Serializer\CallbackSerializer;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\SerializerManager;
use Spiral\Serializer\SerializerRegistryInterface;

final class SerializerBootloaderTest extends TestCase
{
    private Container $container;

    public function testDefaultSerializerIsConfigured(): void
    {
        $this->configureSerializer([
            'json' => new JsonSerializer(),
            'serializer' => new PhpSerializer(),
        ]);

        $manager = $this->container->get(SerializerManager::class);
        self::assertInstanceOf(SerializerManager::class, $manager);
        self::assertInstanceOf(PhpSerializer::class, $manager->getSerializer('serializer'));
        self::assertInstanceOf(JsonSerializer::class, $manager->getSerializer('json'));

        $this->expectException(SerializerNotFoundException::class);
        $manager->getSerializer('foo');
    }

    public function testAddSerializer(): void
    {
        $this->configureSerializer();

        $registry = $this->container->get(SerializerRegistryInterface::class);
        $registry->register('callback', new CallbackSerializer(fn() => null, fn() => null));

        $manager = $this->container->get(SerializerManager::class);
        self::assertInstanceOf(SerializerManager::class, $manager);
        self::assertInstanceOf(CallbackSerializer::class, $manager->getSerializer('callback'));

        $this->expectException(SerializerNotFoundException::class);
        $manager->getSerializer('foo');
        $manager->getSerializer('serializer');
        $manager->getSerializer('json');
    }

    public function testAddSerializerByClassString(): void
    {
        $this->configureSerializer(['json' => PhpSerializer::class]);

        $manager = $this->container->get(SerializerManager::class);
        self::assertInstanceOf(PhpSerializer::class, $manager->getSerializer('json'));
    }

    public function testAddSerializerByAutowire(): void
    {
        $this->configureSerializer(['json' => new Autowire(PhpSerializer::class)]);

        $manager = $this->container->get(SerializerManager::class);
        self::assertInstanceOf(PhpSerializer::class, $manager->getSerializer('json'));
    }

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    private function configureSerializer(array $serializers = []): void
    {
        $this->container->bind(SerializerConfig::class, new SerializerConfig([
            'default' => 'json',
            'serializers' => $serializers,
        ]));
        $bootloader = new SerializerBootloader($this->createMock(ConfiguratorInterface::class), $this->container, $this->container);

        $this->container->bindSingleton(SerializerRegistryInterface::class, [$bootloader, 'initSerializerRegistry']);
        $this->container->bindSingleton(SerializerManager::class, [$bootloader, 'initSerializerManager']);
    }
}
