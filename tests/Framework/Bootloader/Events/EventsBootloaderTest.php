<?php

declare(strict_types=1);

namespace Framework\Bootloader\Events;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\FinalizerInterface;
use Spiral\Events\Bootloader\EventsBootloader;
use Spiral\Events\Config\EventsConfig;
use Spiral\Events\EventDispatcherAwareInterface;
use Spiral\Events\ListenerFactoryInterface;
use Spiral\Events\ListenerLocator;
use Spiral\Events\ListenerProcessorRegistry;
use Spiral\Tests\Framework\BaseTest;

final class EventsBootloaderTest extends BaseTest
{
    public function testListenerFactoryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            ListenerFactoryInterface::class,
            ListenerFactoryInterface::class
        );
    }

    public function testDefaultConfig(): void
    {
        $config = $this->getConfig(EventsConfig::CONFIG);

        $this->assertArrayHasKey('listeners', $config);
        $this->assertArrayHasKey('processors', $config);
    }

    public function testProcessorsShouldBeProcessed(): void
    {
        $registry = new ListenerProcessorRegistry();
        $bootloader = $this->getContainer()->get(EventsBootloader::class);

        $kernel = $this->getContainer()->get(AbstractKernel::class);
        $this->assertFalse($registry->isProcessed());
        $this->assertCount(0, $registry->getProcessors());

        $bootloader->boot($kernel, $registry, $this->createMock(FinalizerInterface::class));
        $kernel->run();

        $this->assertTrue($registry->isProcessed());
        $this->assertCount(2, $registry->getProcessors());
    }

    public function testEventDispatcherForFinalizerShouldBeSet(): void
    {
        $bootloader = $this->getContainer()->get(EventsBootloader::class);
        $kernel = $this->getContainer()->get(AbstractKernel::class);
        $registry = new ListenerProcessorRegistry();

        $finalizer = \Mockery::mock(FinalizerInterface::class, EventDispatcherAwareInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $finalizer->shouldReceive('setEventDispatcher')->once()->with($dispatcher);

        $bootloader->boot($kernel, $registry, $finalizer, $dispatcher);
    }

    public function testEventDispatcherForFinalizerShouldNotBeSet(): void
    {
        $bootloader = $this->getContainer()->get(EventsBootloader::class);
        $kernel = $this->getContainer()->get(AbstractKernel::class);
        $registry = new ListenerProcessorRegistry();

        $finalizer = \Mockery::mock(FinalizerInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $finalizer->shouldReceive('setEventDispatcher')->never();

        $bootloader->boot($kernel, $registry, $finalizer, $dispatcher);
    }
}
