<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Failed;

use Mockery as m;
use Spiral\Exceptions\ErrorHandlerInterface;
use Spiral\Queue\Failed\LogFailedJobHandler;
use Spiral\Tests\Queue\TestCase;

final class LogFailedJobHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $handler = new LogFailedJobHandler(
            $errHandler = m::mock(ErrorHandlerInterface::class)
        );

        $e = new \Exception('Something went wrong');

        $errHandler->shouldReceive('shouldReport')->once()->with($e);
        $errHandler->shouldReceive('report')->once()->with($e);

        $handler->handle('foo', 'bar', 'baz', [], $e);
    }
}
