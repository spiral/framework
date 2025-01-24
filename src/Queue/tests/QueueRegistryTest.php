<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\HandlerInterface;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\QueueRegistry;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\SerializerInterface;
use Spiral\Serializer\SerializerManager;
use Spiral\Serializer\SerializerRegistry;
use Spiral\Serializer\SerializerRegistryInterface;

final class QueueRegistryTest extends TestCase
{
    private Container $mockContainer;

    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|HandlerRegistryInterface */
    private $fallbackHandlers;

    private QueueRegistry $registry;

    public static function serializersDataProvider(): \Traversable
    {
        // serializer name
        yield [new SerializerRegistry(['some' => new JsonSerializer()]), 'some'];

        // class-string
        yield [new SerializerRegistry(['some' => new JsonSerializer()]), JsonSerializer::class];

        // class
        yield [new SerializerRegistry(['some' => new JsonSerializer()]), new JsonSerializer()];

        // autowire
        yield [new SerializerRegistry(['some' => new JsonSerializer()]), new Autowire(JsonSerializer::class)];

        // adding by class-string
        yield [new SerializerRegistry(), JsonSerializer::class];

        // adding by class
        yield [new SerializerRegistry(), new JsonSerializer()];

        // adding by autowire
        yield [new SerializerRegistry(), new Autowire(JsonSerializer::class)];
    }

    public function testGetsHandlerForNotRegisteredJobType(): void
    {
        $this->fallbackHandlers->shouldReceive('getHandler')->once()->with('foo')
            ->andReturn($handler = m::mock(HandlerInterface::class));

        self::assertSame($handler, $this->registry->getHandler('foo'));
    }

    public function testGetsRegisteredHandler(): void
    {
        $handler = m::mock(HandlerInterface::class);
        $this->registry->setHandler('foo', $handler);

        self::assertSame($handler, $this->registry->getHandler('foo'));
    }

    public function testGetsRegisteredHandlerFromContainer(): void
    {
        $handler = m::mock(HandlerInterface::class);

        $this->registry->setHandler('foo', 'bar');
        $this->mockContainer->bind('bar', $handler);

        self::assertSame($handler, $this->registry->getHandler('foo'));
    }

    public function testDefaultSerializerIsNull(): void
    {
        $this->mockContainer->bind(QueueConfig::class, new QueueConfig());

        $this->mockContainer->bind(SerializerManager::class, new SerializerManager(new SerializerRegistry([
            'serializer' => new PhpSerializer(),
            'json' => new JsonSerializer(),
        ]), 'json'));

        self::assertInstanceOf(JsonSerializer::class, $this->registry->getSerializer());
    }

    #[DataProvider('serializersDataProvider')]
    public function testDefaultSerializer(
        SerializerRegistry $registry,
        string|SerializerInterface|Autowire $serializer,
    ): void {
        $this->mockContainer->bind(QueueConfig::class, new QueueConfig(['defaultSerializer' => $serializer]));
        $this->mockContainer->bind(SerializerRegistryInterface::class, $registry);

        self::assertInstanceOf(JsonSerializer::class, $this->registry->getSerializer());
    }

    #[DataProvider('serializersDataProvider')]
    public function testSerializer(SerializerRegistry $registry, string|SerializerInterface|Autowire $serializer): void
    {
        $this->mockContainer->bind(SerializerRegistryInterface::class, $registry);

        self::assertFalse($this->registry->hasSerializer('foo'));

        $this->registry->setSerializer('foo', $serializer);

        self::assertTrue($this->registry->hasSerializer('foo'));
        self::assertInstanceOf(SerializerInterface::class, $this->registry->getSerializer('foo'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockContainer = new Container();

        $this->registry = new QueueRegistry(
            $this->mockContainer,
            $this->mockContainer,
            $this->fallbackHandlers = m::mock(HandlerRegistryInterface::class),
        );
    }
}
