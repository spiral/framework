<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor;

use Mockery as m;
use Spiral\Queue\HandlerInterface;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\Interceptor\Core;
use Spiral\Tests\Queue\TestCase;

final class CoreTest extends TestCase
{
    public function testCallAction(): void
    {
        $core = new Core(
            $registry = m::mock(HandlerRegistryInterface::class)
        );

        $registry->shouldReceive('getHandler')->with('foo')->once()
            ->andReturn($handler = m::mock(HandlerInterface::class));

        $handler->shouldReceive('handle')->once()
            ->with('foo', 'job-id', ['baz' => 'baf']);

        $core->callAction('foo', 'bar', [
            'id' => 'job-id',
            'payload' => ['baz' => 'baf'],
        ]);
    }
}
