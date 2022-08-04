<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor\Push;

use Mockery as m;
use Spiral\Queue\Interceptor\Push\Core;
use Spiral\Queue\Options;
use Spiral\Queue\QueueInterface;
use Spiral\Tests\Queue\TestCase;

final class CoreTest extends TestCase
{
    public function testCallAction(): void
    {
        $core = new Core(
            $queue = m::mock(QueueInterface::class)
        );

        $options = new Options();

        $queue->shouldReceive('push')->once()
            ->with('foo', ['baz' => 'baf'], $options);

        $core->callAction('foo', 'bar', [
            'id' => 'job-id',
            'payload' => ['baz' => 'baf'],
            'options' => $options
        ]);
    }
}
