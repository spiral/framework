<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use Psr\Container\ContainerInterface;
use Spiral\Queue\HandlerInterface;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\QueueRegistry;

final class QueueRegistryTest extends TestCase
{
    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|ContainerInterface */
    private $mockContainer;
    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|HandlerRegistryInterface */
    private $fallbackHandlers;
    /** @var QueueRegistry */
    private $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new QueueRegistry(
            $this->mockContainer = m::mock(ContainerInterface::class),
            $this->fallbackHandlers = m::mock(HandlerRegistryInterface::class)
        );
    }

    public function testGetsHandlerForNotRegisteredJobType()
    {
        $this->fallbackHandlers->shouldReceive('getHandler')->once()->with('foo')
            ->andReturn($handler = m::mock(HandlerInterface::class));

        $this->assertSame($handler, $this->registry->getHandler('foo'));
    }

    public function testGetsRegisteredHandler()
    {
        $handler = m::mock(HandlerInterface::class);
        $this->registry->setHandler('foo', $handler);

        $this->assertSame($handler, $this->registry->getHandler('foo'));
    }

    public function testGetsRegisteredHandlerFromContainer()
    {
        $this->registry->setHandler('foo', 'bar');
        $this->mockContainer->shouldReceive('get')->once()->with('bar')
            ->andReturn($handler = m::mock(HandlerInterface::class));

        $this->assertSame($handler, $this->registry->getHandler('foo'));
    }
}
