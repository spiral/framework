<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor\Consume;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Failed\FailedJobHandlerInterface;
use Spiral\Queue\Interceptor\Consume\ErrorHandlerInterceptor;
use Spiral\Tests\Queue\TestCase;

final class ErrorHandlerInterceptorTest extends TestCase
{
    public static function payloadDataProvider(): \Traversable
    {
        yield [['baz' => 'baf']];
        yield [new \stdClass()];
        yield ['some string'];
        yield [123];
        yield [null];
    }

    #[DataProvider('payloadDataProvider')]
    public function testProcessError(mixed $payload): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Something went wrong');

        $interceptor = new ErrorHandlerInterceptor(
            $handler = m::mock(FailedJobHandlerInterface::class),
        );

        if (!\is_array($payload)) {
            $this->markTestIncomplete('FailedJobHandlerInterface does not support non-array payloads');
            return;
        }

        $parameters = ['driver' => 'sync', 'queue' => 'default', 'payload' => $payload];
        $exception = new \Exception('Something went wrong');
        $core = m::mock(CoreInterface::class);
        $core->shouldReceive('callAction')
            ->once()
            ->with('foo', 'bar', $parameters)
            ->andThrow($exception);

        $handler->shouldReceive('handle')
            ->once()
            ->with('sync', 'default', 'foo', $payload, $exception);

        $interceptor->process('foo', 'bar', $parameters, $core);
    }

    public function testHandlerShouldBeHandledWithoutError(): void
    {
        $interceptor = new ErrorHandlerInterceptor(
            m::mock(FailedJobHandlerInterface::class),
        );

        $parameters = ['driver' => 'sync', 'queue' => 'default', 'payload' => ['baz' => 'bar']];
        $core = m::mock(CoreInterface::class);
        $core->shouldReceive('callAction')
            ->once()
            ->with('foo', 'bar', $parameters)
            ->andReturnNull();

        $interceptor->process('foo', 'bar', $parameters, $core);
    }
}
