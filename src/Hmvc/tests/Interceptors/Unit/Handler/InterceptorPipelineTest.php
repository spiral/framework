<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\Handler\InterceptorPipeline;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;
use Spiral\Tests\Interceptors\Unit\Stub\ExceptionInterceptor;
use Spiral\Tests\Interceptors\Unit\Stub\MultipleCallNextInterceptor;

final class InterceptorPipelineTest extends TestCase
{
    public function testInterceptorCallingEventShouldBeDispatched(): void
    {
        $context = $this->createPathContext(['test', 'test2']);
        $interceptor = new class implements InterceptorInterface {
            public function intercept(CallContextInterface $context, HandlerInterface $handler): mixed
            {
                return null;
            }
        };
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                new \Spiral\Interceptors\Event\InterceptorCalling(
                    $context,
                    $interceptor,
                )
            );
        $pipeline = $this->createPipeline(interceptors: [$interceptor], dispatcher: $dispatcher);

        $pipeline->withHandler(
            new class implements HandlerInterface {
                public function handle(CallContext $context): mixed
                {
                    return null;
                }
            }
        )->handle($context, []);
    }

    public function testCallActionWithoutCore(): void
    {
        $pipeline = $this->createPipeline();

        self::expectExceptionMessage('Unable to invoke pipeline without last handler.');

        $pipeline->handle($this->createPathContext(['controller', 'action']));
    }

    public function testHandleWithoutHandler(): void
    {
        $pipeline = $this->createPipeline();

        self::expectExceptionMessage('Unable to invoke pipeline without last handler.');

        $pipeline->handle(new CallContext(Target::fromPathArray(['controller', 'action'])));
    }

    public function testHandleWithHandler(): void
    {
        $ctx = new CallContext(Target::fromPathArray(['controller', 'action']));
        $mock = self::createMock(HandlerInterface::class);
        $mock->expects(self::exactly(2))
            ->method('handle')
            ->with($ctx)
            ->willReturn('test1', 'test2');
        $pipeline = $this->createPipeline([new MultipleCallNextInterceptor(2)], $mock);

        $result = $pipeline->handle($ctx);

        self::assertSame(['test1', 'test2'], $result);
    }

    /**
     * Multiple call of same the handler inside the pipeline must invoke the same interceptor.
     */
    public function testCallHandlerTwice(): void
    {
        $mock = self::createMock(InterceptorInterface::class);
        $mock->expects(self::exactly(2))
            ->method('intercept')
            ->willReturn('foo', 'bar');

        $pipeline = $this->createPipeline([
            new MultipleCallNextInterceptor(2),
            $mock,
            new ExceptionInterceptor(),
        ], self::createMock(HandlerInterface::class));

        $result = $pipeline->handle($this->createPathContext(['controller', 'action']));
        self::assertSame(['foo', 'bar'], $result);
    }

    /**
     * @param array<InterceptorInterface> $interceptors
     */
    private function createPipeline(
        array $interceptors = [],
        HandlerInterface|null $lastHandler = null,
        EventDispatcherInterface|null $dispatcher = null,
    ): InterceptorPipeline {
        $pipeline = new InterceptorPipeline($dispatcher);

        $lastHandler instanceof HandlerInterface and $pipeline = $pipeline->withHandler($lastHandler);

        foreach ($interceptors as $interceptor) {
            $pipeline->addInterceptor($interceptor);
        }

        return $pipeline;
    }

    public function createPathContext(array $path = []): CallContext
    {
        return new CallContext(Target::fromPathArray($path));
    }
}
