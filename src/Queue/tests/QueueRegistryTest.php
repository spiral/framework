<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use Psr\Container\ContainerInterface;
use Spiral\Queue\HandlerInterface;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\QueueRegistry;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\SerializerRegistry;
use Spiral\Serializer\SerializerRegistryInterface;

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

    public function testGetsHandlerForNotRegisteredJobType(): void
    {
        $this->fallbackHandlers->shouldReceive('getHandler')->once()->with('foo')
            ->andReturn($handler = m::mock(HandlerInterface::class));

        $this->assertSame($handler, $this->registry->getHandler('foo'));
    }

    public function testGetsRegisteredHandler(): void
    {
        $handler = m::mock(HandlerInterface::class);
        $this->registry->setHandler('foo', $handler);

        $this->assertSame($handler, $this->registry->getHandler('foo'));
    }

    public function testGetsRegisteredHandlerFromContainer(): void
    {
        $this->registry->setHandler('foo', 'bar');
        $this->mockContainer->shouldReceive('get')->once()->with('bar')
            ->andReturn($handler = m::mock(HandlerInterface::class));

        $this->assertSame($handler, $this->registry->getHandler('foo'));
    }

    public function testSerializerFormat(): void
    {
        $registry = new SerializerRegistry([
            'some' => new JsonSerializer(),
            'other' => new JsonSerializer(),
        ]);

        $this->mockContainer->shouldReceive('get')->times(2)->with(SerializerRegistryInterface::class)
            ->andReturn($registry);

        $this->assertFalse($this->registry->hasSerializer('foo'));
        $this->assertFalse($this->registry->hasSerializer('bar'));

        $this->registry->setSerializerFormat('foo', 'some');
        $this->registry->setSerializerFormat('bar', 'other');

        $this->assertTrue($this->registry->hasSerializer('foo'));
        $this->assertTrue($this->registry->hasSerializer('bar'));

        $this->assertSame('some', $this->registry->getSerializerFormat('foo'));
        $this->assertSame('other', $this->registry->getSerializerFormat('bar'));
    }
}
