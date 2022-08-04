<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Options;
use Spiral\Queue\Queue;

final class QueueTest extends TestCase
{
    public function testPush(): void
    {
        $queue = new Queue(
            $core = m::mock(CoreInterface::class)
        );

        $options = new Options();

        $core->shouldReceive('callAction')->once()
            ->with('foo', 'push', [
                'payload' => ['baz' => 'baf'],
                'options' => $options
            ])
            ->andReturn('task-id');

        $id = $queue->push('foo', ['baz' => 'baf'], $options);

        $this->assertSame('task-id', $id);
    }
}
