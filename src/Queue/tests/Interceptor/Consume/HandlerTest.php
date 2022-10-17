<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor;

use Mockery as m;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Interceptor\Consume\Handler;
use Spiral\Tests\Queue\TestCase;

final class HandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $handler = new Handler($core = m::mock(CoreInterface::class));

        $core->shouldReceive('callAction')
            ->once()
            ->with('foo', 'handle', [
                'driver' => 'sync',
                'queue' => 'default',
                'id' => 'job-id',
                'payload' => ['baz' => 'bar'],
                'headers' => ['some' => 'data'],
            ]);

        $handler->handle('foo', 'sync', 'default', 'job-id', ['baz' => 'bar'], ['some' => 'data']);
    }
}
