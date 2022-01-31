<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Failed;

use Mockery as m;
use Spiral\Queue\Failed\LogFailedJobHandler;
use Spiral\Tests\Queue\TestCase;
use Spiral\Snapshots\SnapshotterInterface;

final class LogFailedJobHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $handler = new LogFailedJobHandler(
            $snapshotter = m::mock(SnapshotterInterface::class)
        );

        $e = new \Exception('Something went wrong');

        $snapshotter->shouldReceive('register')->once()->with($e);

        $handler->handle('foo', 'bar', 'baz', [], $e);
    }
}
