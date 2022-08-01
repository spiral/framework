<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Queue\HandlerInterface;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\QueueRegistry;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\SerializerInterface;
use Spiral\Serializer\SerializerRegistry;
use Spiral\Serializer\SerializerRegistryInterface;

final class QueueRegistryTest extends TestCase
{
    private Container $mockContainer;
    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|HandlerRegistryInterface */
    private $fallbackHandlers;
    /** @var QueueRegistry */
    private $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new QueueRegistry(
            $this->mockContainer = new Container(),
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
        $handler = m::mock(HandlerInterface::class);

        $this->registry->setHandler('foo', 'bar');
        $this->mockContainer->bind('bar', $handler);

        $this->assertSame($handler, $this->registry->getHandler('foo'));
    }

    /** @dataProvider serializersDataProvider */
    public function testSerializer(
        SerializerRegistry $registry,
        string|SerializerInterface|Autowire $serializer,
        string $expectedFormat
    ): void {
        $this->mockContainer->bind(SerializerRegistryInterface::class, $registry);

        $this->assertFalse($this->registry->hasSerializer('foo'));

        $this->registry->setSerializer('foo', $serializer);

        $this->assertTrue($this->registry->hasSerializer('foo'));

        $this->assertSame($expectedFormat, $this->registry->getSerializerFormat('foo'));
    }

    public function serializersDataProvider(): \Traversable
    {
        // serializer name
        yield [new SerializerRegistry(['some' => new JsonSerializer()]), 'some', 'some'];

        // class-string
        yield [new SerializerRegistry(['some' => new JsonSerializer()]), JsonSerializer::class, 'some'];

        // class
        yield [new SerializerRegistry(['some' => new JsonSerializer()]), new JsonSerializer(), 'some'];

        // autowire
        yield [new SerializerRegistry(['some' => new JsonSerializer()]), new Autowire(JsonSerializer::class), 'some'];

        // adding by class-string
        yield [new SerializerRegistry(), JsonSerializer::class, JsonSerializer::class];

        // adding by class
        yield [new SerializerRegistry(), new JsonSerializer(), JsonSerializer::class];

        // adding by autowire
        yield [new SerializerRegistry(), new Autowire(JsonSerializer::class), JsonSerializer::class];

        yield [new SerializerRegistry(), new Autowire(JsonSerializer::class), JsonSerializer::class];
    }
}
