<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Driver;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Driver\SyncDriver;
use Spiral\Queue\Interceptor\Consume\Handler;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\NullTracerFactory;
use Spiral\Telemetry\TracerInterface;
use Spiral\Tests\Queue\TestCase;

final class SyncDriverTest extends TestCase
{
    private SyncDriver $queue;
    private m\LegacyMockInterface|m\MockInterface|CoreInterface $core;
    private m\LegacyMockInterface|m\MockInterface|UuidFactoryInterface $factory;
    private UuidFactoryInterface $realfactory;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        $container->bind(TracerInterface::class, new NullTracer($container));

        $this->realfactory = Uuid::getFactory();
        Uuid::setFactory($this->factory = m::mock(UuidFactoryInterface::class));

        $this->queue = new SyncDriver(
            new Handler(
                $this->core = m::mock(CoreInterface::class),
                new NullTracerFactory($container)
            )
        );
    }

    #[DataProvider('payloadDataProvider')]
    public function testJobShouldBePushed(mixed $payload): void
    {
        $this->factory->shouldReceive('uuid4')
            ->andReturn($uuid = (new UuidFactory())->uuid4());

        $this->core->shouldReceive('callAction')
            ->withSomeOfArgs('foo', [
                'driver' => 'sync',
                'queue' => 'default',
                'id' => $uuid->toString(),
                'payload' => $payload,
                'headers' => []
            ])
            ->once();

        $id = $this->queue->push('foo', $payload);

        $this->assertSame($uuid->toString(), $id);
    }

    public static function payloadDataProvider(): \Traversable
    {
        yield [['baz' => 'baf']];
        yield [new \stdClass()];
        yield ['some string'];
        yield [123];
        yield [null];
    }

    protected function tearDown(): void
    {
        Uuid::setFactory($this->realfactory);
        parent::tearDown();
    }
}
