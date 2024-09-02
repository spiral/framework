<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\ResolverInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\Exception\TargetCallException;
use Spiral\Interceptors\Handler\AutowireHandler;
use Spiral\Tests\Interceptors\Unit\Stub\TestService;

final class AutowireHandlerTest extends TestCase
{
    public function testHandleReflectionMethodFromExtendedAbstractClass(): void
    {
        $c = new Container();
        $handler = new AutowireHandler($c);
        // Call Context
        $ctx = (new CallContext(Target::fromPair(TestService::class, 'parentMethod')))
            ->withArguments(['HELLO']);

        $result = $handler->handle($ctx);

        self::assertSame('hello', $result);
    }

    public function testHandleReflectionFunction(): void
    {
        $c = new Container();
        $container = self::createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('get')
            ->with(ResolverInterface::class)
            ->willReturn($c);
        $handler = new AutowireHandler($container);
        // Call Context
        $ctx = new CallContext(Target::fromReflectionFunction(new \ReflectionFunction('strtoupper')));
        $ctx = $ctx->withArguments(['hello']);

        $result = $handler->handle($ctx);

        self::assertSame('HELLO', $result);
    }

    public function testHandleReflectionMethodWithObject(): void
    {
        $c = new Container();
        $container = self::createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('get')
            ->with(ResolverInterface::class)
            ->willReturn($c);
        $handler = new AutowireHandler($container);
        // Call Context
        $service = new TestService();
        $ctx = (new CallContext(Target::fromPair($service, 'parentMethod')))
            ->withArguments(['HELLO']);

        $result = $handler->handle($ctx);

        self::assertSame('hello', $result);
    }

    public function testWithoutResolvingFromPath(): void
    {
        $container = self::createMock(ContainerInterface::class);

        $handler = new AutowireHandler($container);

        self::expectException(TargetCallException::class);
        self::expectExceptionMessage('Reflection not provided for target');

        $handler->handle(new CallContext(Target::fromPathString('foo')));
    }

    public function testWithoutReflectionWithCallableArray(): void
    {
        $handler = $this->createHandler();

        self::expectException(TargetCallException::class);
        self::expectExceptionMessage(
            \sprintf('Reflection not provided for target `%s.increment`.', TestService::class),
        );

        $handler->handle(new CallContext(Target::fromPathArray([TestService::class, 'increment'])));
    }

    public function testUsingResolver(): void
    {
        $handler = $this->createHandler();
        $ctx = new CallContext(
            Target::fromReflectionFunction(new \ReflectionFunction(fn (string $value):string => \strtoupper($value)))
        );
        $ctx = $ctx->withArguments(['word' => 'world!', 'value' => 'hello']);

        $result = $handler->handle($ctx);

        self::assertSame('HELLO', $result);
    }

    public function createHandler(array $definitions = []): AutowireHandler
    {
        $container = new Container();
        foreach ($definitions as $id => $definition) {
            $container->bind($id, $definition);
        }

        return new AutowireHandler(
            $container,
        );
    }
}
