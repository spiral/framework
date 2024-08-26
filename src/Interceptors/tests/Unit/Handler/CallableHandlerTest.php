<?php

declare(strict_types=1);

namespace Interceptors\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\Handler\CallableHandler;
use Spiral\Tests\Interceptors\Unit\Stub\TestService;

final class CallableHandlerTest extends TestCase
{
    public function testHandleReflectionMethodFromExtendedAbstractClass(): void
    {
        $handler = $this->createHandler();
        // Call Context
        $ctx = (new CallContext(Target::fromPair(TestService::class, 'parentMethod')))
            ->withArguments(['HELLO']);

        // it's not a PHP callable
        $this->expectException(\RuntimeException::class);
        $handler->handle($ctx);
    }

    public function testHandleReflectionFunction(): void
    {
        $handler = $this->createHandler();
        // Call Context
        $ctx = new CallContext(Target::fromClosure(\strtoupper(...)));
        $ctx = $ctx->withArguments(['hello']);

        $result = $handler->handle($ctx);

        self::assertSame('HELLO', $result);
    }

    public function testInvokeArrayCallable(): void
    {
        $handler = $this->createHandler();
        // Call Context
        $service = new TestService();
        $ctx = (new CallContext(Target::fromPair($service, 'parentMethod')))
            ->withArguments(['HELLO']);

        $result = $handler->handle($ctx);

        self::assertSame('hello', $result);
    }

    public function testInvokeFromReflectionWithNamedArguments(): void
    {
        $handler = $this->createHandler();
        $ctx = new CallContext(
            Target::fromReflectionFunction(new \ReflectionFunction(fn(string $value): string => \strtoupper($value)))
        );
        $ctx = $ctx->withArguments(['value' => 'hello']);

        $result = $handler->handle($ctx);

        self::assertSame('HELLO', $result);
    }

    public function createHandler(): CallableHandler
    {
        return new CallableHandler();
    }
}
