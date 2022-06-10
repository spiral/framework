<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Bootloader;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Serializer\Bootloader\SerializerBootloader;
use Spiral\Serializer\Exception\SerializerNotFoundException;
use Spiral\Serializer\Serializer\CallbackSerializer;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\SerializerCollection;
use Spiral\Serializer\SerializerManager;

final class SerializerBootloaderTest extends TestCase
{
    public function testDefaultSerializerIsConfigured(): void
    {
        $bootloader = new SerializerBootloader();

        $container = new Container();
        $container->bindSingleton(SerializerCollection::class, SerializerCollection::class);
        $container->bindSingleton(SerializerManager::class, [$bootloader, 'initSerializer']);

        $manager = $container->get(SerializerManager::class);
        $this->assertInstanceOf(SerializerManager::class, $manager);
        $this->assertInstanceOf(PhpSerializer::class, $manager->getSerializer('serialize'));
        $this->assertInstanceOf(JsonSerializer::class, $manager->getSerializer('json'));

        $this->expectException(SerializerNotFoundException::class);
        $manager->getSerializer('foo');
    }

    public function testConfigureSerializer(): void
    {
        $collection = new SerializerCollection(['callback', new CallbackSerializer(fn () => null, fn () => null)]);

        $bootloader = new SerializerBootloader();
        $container = new Container();
        $container->bindSingleton(SerializerCollection::class, $collection);
        $container->bindSingleton(SerializerManager::class, [$bootloader, 'initSerializer']);

        $manager = $container->get(SerializerManager::class);
        $this->assertInstanceOf(SerializerManager::class, $manager);
        $this->assertInstanceOf(CallbackSerializer::class, $manager->getSerializer('callback'));

        $this->expectException(SerializerNotFoundException::class);
        $manager->getSerializer('foo');
        $manager->getSerializer('serialize');
        $manager->getSerializer('json');
    }
}
