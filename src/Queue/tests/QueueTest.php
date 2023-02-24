<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Mockery as m;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Options;
use Spiral\Queue\Queue;

final class QueueTest extends TestCase
{
    /**
     * @dataProvider pushDataProvider
     */
    public function testPush(mixed $payload, mixed $options): void
    {
        $queue = new Queue(
            $core = m::mock(CoreInterface::class)
        );

        $core->shouldReceive('callAction')->once()
            ->with('foo', 'push', [
                'payload' => $payload,
                'options' => $options
            ])
            ->andReturn('task-id');

        $id = $queue->push('foo', $payload, $options);

        $this->assertSame('task-id', $id);
    }

    public function pushDataProvider(): \Traversable
    {
        yield [['baz' => 'baf'], new Options()];
        yield [new \stdClass(), new Options()];
        yield [new \stdClass(), new \stdClass()];
    }
}
