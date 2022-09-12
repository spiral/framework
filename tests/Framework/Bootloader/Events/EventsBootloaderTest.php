<?php

declare(strict_types=1);

namespace Framework\Bootloader\Events;

use Mockery as m;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\FinalizerInterface;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Events\Bootloader\EventsBootloader;
use Spiral\Events\Config\EventsConfig;
use Spiral\Events\EventDispatcherAwareInterface;
use Spiral\Events\ListenerFactoryInterface;
use Spiral\Events\ListenerProcessorRegistry;
use Spiral\Events\Processor\ProcessorInterface;
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

        $this->bootBootloader(
            bootloader: $bootloader,
            kernel: $kernel,
            registry: $registry,
            config: new EventsConfig([
                'processors' => [
                    $processor1 = m::mock(ProcessorInterface::class),
                    $processor2 = m::mock(ProcessorInterface::class),
                ],
            ])
        );

        $processor1->shouldReceive('process')->once();
        $processor2->shouldReceive('process')->once();

        $kernel->run();

        $this->assertTrue($registry->isProcessed());
        $this->assertCount(2, $registry->getProcessors());
    }

    public function testStringProcessorsShouldBeProcessed(): void
    {
        $bootloader = $this->getContainer()->get(EventsBootloader::class);
        $container = m::mock(ContainerInterface::class);

        $container->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn(
                $processor = m::mock(ProcessorInterface::class)
            );

        $kernel = $this->getContainer()->get(AbstractKernel::class);

        $this->bootBootloader(
            container: $container,
            bootloader: $bootloader,
            kernel: $kernel,
            config: new EventsConfig([
                'processors' => [
                    'foo',
                ],
            ])
        );

        $processor->shouldReceive('process')->once();

        $kernel->run();
    }

    public function testAutowireProcessorsShouldBeProcessed(): void
    {
        $bootloader = $this->getContainer()->get(EventsBootloader::class);
        $factory = m::mock(FactoryInterface::class);

        $factory->shouldReceive('make')
            ->once()
            ->with('foo', [])
            ->andReturn(
                $processor = m::mock(ProcessorInterface::class)
            );

        $kernel = $this->getContainer()->get(AbstractKernel::class);

        $this->bootBootloader(
            factory: $factory,
            bootloader: $bootloader,
            kernel: $kernel,
            config: new EventsConfig([
                'processors' => [
                    new Container\Autowire('foo'),
                ],
            ])
        );

        $processor->shouldReceive('process')->once();

        $kernel->run();
    }

    public function testEventDispatcherForFinalizerShouldBeSet(): void
    {
        $bootloader = $this->getContainer()->get(EventsBootloader::class);
        $kernel = $this->getContainer()->get(AbstractKernel::class);

        $finalizer = m::mock(FinalizerInterface::class, EventDispatcherAwareInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $finalizer->shouldReceive('setEventDispatcher')->once()->with($dispatcher);

        $this->bootBootloader(
            bootloader: $bootloader,
            kernel: $kernel,
            finalizer: $finalizer,
            eventDispatcher: $dispatcher
        );
    }

    public function testEventDispatcherForFinalizerShouldNotBeSet(): void
    {
        $bootloader = $this->getContainer()->get(EventsBootloader::class);
        $kernel = $this->getContainer()->get(AbstractKernel::class);

        $finalizer = m::mock(FinalizerInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $finalizer->shouldReceive('setEventDispatcher')->never();

        $this->bootBootloader(
            bootloader: $bootloader,
            kernel: $kernel,
            finalizer: $finalizer,
            eventDispatcher: $dispatcher
        );
    }

    public function bootBootloader(
        EventsBootloader $bootloader,
        AbstractKernel $kernel,
        ContainerInterface $container = new Container(),
        FactoryInterface $factory = new Container(),
        ListenerProcessorRegistry $registry = new ListenerProcessorRegistry(),
        ?FinalizerInterface $finalizer = null,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?EventsConfig $config = new EventsConfig(),
    ): void {
        $finalizer ??= m::mock(FinalizerInterface::class);
        $eventDispatcher ??= m::mock(EventDispatcherInterface::class);

        $bootloader->boot(
            $container,
            $factory,
            $config,
            $kernel,
            $registry,
            $finalizer,
            $eventDispatcher
        );
    }
}
