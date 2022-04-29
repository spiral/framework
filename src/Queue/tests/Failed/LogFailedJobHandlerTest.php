<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Failed;

use Mockery as m;
use Spiral\Exceptions\ExceptionHandlerInterface;
use Spiral\Exceptions\ExceptionReporterInterface;
use Spiral\Queue\Failed\LogFailedJobHandler;
use Spiral\Tests\Queue\TestCase;

final class LogFailedJobHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $handler = new LogFailedJobHandler(
            $errHandler = m::mock(ExceptionReporterInterface::class)
        );

        $e = new \Exception('Something went wrong');

        $errHandler->shouldReceive('report')->once()->with($e);

        $handler->handle('foo', 'bar', 'baz', [], $e);
    }
}
