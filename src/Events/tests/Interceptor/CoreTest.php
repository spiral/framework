<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Interceptor;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Events\Interceptor\Core;

final class CoreTest extends TestCase
{
    public function testCallAction(): void
    {
        $event = new class() {};

        $dispatcher = m::mock(EventDispatcherInterface::class);
        $dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with($event)
            ->andReturn($event);

        $core = new Core($dispatcher);

        self::assertSame($event, $core->callAction('', '', ['event' => $event]));
    }
}
