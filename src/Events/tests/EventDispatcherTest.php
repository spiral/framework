<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Interceptor;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\CoreInterface;
use Spiral\Events\EventDispatcher;

final class EventDispatcherTest extends TestCase
{
    public function testDispatch(): void
    {
        $event = new class() {};

        $core = m::mock(CoreInterface::class);
        $core
            ->shouldReceive('callAction')
            ->once()
            ->with(EventDispatcherInterface::class, 'dispatch', ['event' => $event])
            ->andReturn($event);

        $dispatcher = new EventDispatcher($core);

        $this->assertSame($event, $dispatcher->dispatch($event));
    }
}
