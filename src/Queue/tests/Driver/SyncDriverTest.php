<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Driver;

use Mockery as m;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Driver\SyncDriver;
use Spiral\Queue\Interceptor\Consume\Handler;
use Spiral\Queue\Job\ObjectJob;
use Spiral\Tests\Queue\TestCase;

final class SyncDriverTest extends TestCase
{
    private SyncDriver $queue;
    private m\LegacyMockInterface|m\MockInterface|CoreInterface $core;
    private m\LegacyMockInterface|m\MockInterface|UuidFactoryInterface $factory;

    protected function setUp(): void
    {
        parent::setUp();

        Uuid::setFactory($this->factory = m::mock(UuidFactoryInterface::class));

        $this->queue = new SyncDriver(
            new Handler($this->core = m::mock(CoreInterface::class)),
        );
    }

    public function testJobShouldBePushed(): void
    {
        $this->factory->shouldReceive('uuid4')
            ->once()
            ->andReturn($uuid = (new UuidFactory())->uuid4());

        $this->core->shouldReceive('callAction')
            ->withSomeOfArgs('foo', [
                'driver' => 'sync',
                'queue' => 'default',
                'id' => $uuid->toString(),
                'payload' => ['foo' => 'bar'],
                'context' => []
            ])
            ->once();

        $id = $this->queue->push('foo', ['foo' => 'bar']);

        $this->assertSame($uuid->toString(), $id);
    }

    public function testJobObjectShouldBePushed(): void
    {
        $object = new \stdClass();
        $object->foo = 'bar';

        $this->factory->shouldReceive('uuid4')
            ->once()
            ->andReturn($uuid = (new UuidFactory())->uuid4());

        $this->core->shouldReceive('callAction')
            ->withSomeOfArgs(ObjectJob::class, [
                'driver' => 'sync',
                'queue' => 'default',
                'id' => $uuid->toString(),
                'payload' => ['object' => $object],
                'context' => []
            ])
            ->once();

        $id = $this->queue->pushObject($object);

        $this->assertSame($uuid->toString(), $id);
    }
}
