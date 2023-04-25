<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Failed;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Exceptions\ExceptionReporterInterface;
use Spiral\Queue\Failed\LogFailedJobHandler;
use Spiral\Tests\Queue\TestCase;

final class LogFailedJobHandlerTest extends TestCase
{
    #[DataProvider('payloadDataProvider')]
    public function testHandle(mixed $payload): void
    {
        $handler = new LogFailedJobHandler(
            $errHandler = m::mock(ExceptionReporterInterface::class)
        );

        $e = new \Exception('Something went wrong');

        $errHandler->shouldReceive('report')->once()->with($e);

        $handler->handle('foo', 'bar', 'baz', $payload, $e);
    }

    public static function payloadDataProvider(): \Traversable
    {
        yield [['baz' => 'baf']];
        yield [new \stdClass()];
        yield ['some string'];
        yield [123];
        yield [null];
    }
}
