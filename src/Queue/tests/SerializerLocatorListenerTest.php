<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Container;
use Spiral\Core\InvokerInterface;
use Spiral\Queue\Attribute\Serializer;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\JobHandler;
use Spiral\Queue\QueueRegistry;
use Spiral\Queue\SerializerLocatorListener;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\SerializerRegistry;
use Spiral\Serializer\SerializerRegistryInterface;

final class SerializerLocatorListenerTest extends TestCase
{
    public function testListenWithJobTypeFromConfig(): void
    {
        $handler = new class($this->createMock(InvokerInterface::class)) extends JobHandler {};

        $container = new Container();
        $container->bind('test', new PhpSerializer());
        $container->bind(SerializerRegistryInterface::class, SerializerRegistry::class);

        $registry = new QueueRegistry(
            $container,
            $container,
            $this->createMock(HandlerRegistryInterface::class)
        );

        $reader = m::mock(ReaderInterface::class);
        $reader
            ->shouldReceive('firstClassMetadata')
            ->with(m::type(\ReflectionClass::class), Serializer::class)
            ->andReturn(new Serializer('test'));
        $reader
            ->shouldReceive('firstClassMetadata')
            ->with(m::type(\ReflectionClass::class), \Spiral\Queue\Attribute\JobHandler::class)
            ->andReturnNull();

        $listener = new SerializerLocatorListener($reader, $registry, new QueueConfig([
            'registry' => [
                'handlers' => [
                    $handler::class => $handler::class,
                ]
            ]
        ]));
        $listener->listen(new \ReflectionClass($handler::class));

        $this->assertEquals(new PhpSerializer(), $registry->getSerializer($handler::class));
    }

    public function testListenWithJobTypeFromAttribute(): void
    {
        $handler = new class($this->createMock(InvokerInterface::class)) extends JobHandler {};

        $container = new Container();
        $container->bind('test', new PhpSerializer());
        $container->bind(SerializerRegistryInterface::class, SerializerRegistry::class);

        $registry = new QueueRegistry(
            $container,
            $container,
            $this->createMock(HandlerRegistryInterface::class)
        );

        $reader = m::mock(ReaderInterface::class);
        $reader
            ->shouldReceive('firstClassMetadata')
            ->with(m::type(\ReflectionClass::class), Serializer::class)
            ->andReturn(new Serializer('test'));
        $reader
            ->shouldReceive('firstClassMetadata')
            ->with(m::type(\ReflectionClass::class), \Spiral\Queue\Attribute\JobHandler::class)
            ->andReturn(new \Spiral\Queue\Attribute\JobHandler('test'));

        $listener = new SerializerLocatorListener($reader, $registry, new QueueConfig([
            'registry' => [
                'handlers' => [
                    'test' => $handler::class,
                ]
            ]
        ]));
        $listener->listen(new \ReflectionClass($handler::class));

        $this->assertEquals(new PhpSerializer(), $registry->getSerializer('test'));
    }
}
