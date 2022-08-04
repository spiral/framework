<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor\Consume;

use Mockery as m;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Failed\FailedJobHandlerInterface;
use Spiral\Queue\Interceptor\Consume\ErrorHandlerInterceptor;
use Spiral\Tests\Queue\TestCase;

final class ErrorHandlerInterceptorTest extends TestCase
{
    public function testProcessError(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage('Something went wrong');

        $interceptor = new ErrorHandlerInterceptor(
            $handler = m::mock(FailedJobHandlerInterface::class)
        );

        $parameters = ['driver' => 'sync', 'queue' => 'default', 'payload' => ['baz' => 'bar']];
        $exception = new \Exception('Something went wrong');
        $core = m::mock(CoreInterface::class);
        $core->shouldReceive('callAction')
            ->once()
            ->with('foo', 'bar', $parameters)
            ->andThrow($exception);


        $handler->shouldReceive('handle')
            ->once()
            ->with('sync', 'default', 'foo', ['baz' => 'bar'], $exception);

        $interceptor->process('foo', 'bar', $parameters, $core);
    }

    public function testHandlerShouldBeHandledWithoutError(): void
    {
        $interceptor = new ErrorHandlerInterceptor(
            m::mock(FailedJobHandlerInterface::class)
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
