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
use Spiral\Interceptors\Handler\ReflectionHandler;
use Spiral\Tests\Interceptors\Unit\Stub\TestService;

final class ReflectionHandlerTest extends TestCase
{
    public function testHandleReflectionFunction(): void
    {
        $c = new Container();
        $container = self::createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('get')
            ->with(ResolverInterface::class)
            ->willReturn($c);
        $handler = new ReflectionHandler($container, false);
        // Call Context
        $ctx = new CallContext(Target::fromReflection(new \ReflectionFunction('strtoupper')));
        $ctx = $ctx->withArguments(['hello']);

        $result = $handler->handle($ctx);

        self::assertSame('HELLO', $result);
    }

    public function testHandleWrongReflectionFunction(): void
    {
        $handler = $this->createHandler();
        // Call Context
        $ctx = new CallContext(Target::fromReflection(new class extends \ReflectionFunctionAbstract {
            /** @psalm-immutable */
            public function getName(): string
            {
                return 'testReflection';
            }

            public function __toString(): string
            {
                return 'really?';
            }

            public static function export(): void
            {
                // do nothing
            }
        }));

        self::expectException(TargetCallException::class);
        self::expectExceptionMessageMatches('/Action not found for target `testReflection`/');

        $handler->handle($ctx);
    }

    public function testWithoutResolvingFromPathAndReflection(): void
    {
        $container = self::createMock(ContainerInterface::class);

        $handler = new ReflectionHandler($container, false);

        self::expectException(TargetCallException::class);
        self::expectExceptionMessageMatches('/Reflection not provided for target/');

        $handler->handle(new CallContext(Target::fromPathString('foo')));
    }

    public function testWithoutReflectionWithResolvingFromPathWithIncorrectPath(): void
    {
        $handler = $this->createHandler();

        self::expectException(TargetCallException::class);
        self::expectExceptionMessageMatches('/Invalid target path to resolve reflection/');

        $handler->handle(new CallContext(Target::fromPathArray(['foo', 'bar', 'baz'])));
    }

    public function testWithoutReflectionWithResolvingFromPathWithWrongPath(): void
    {
        $handler = $this->createHandler();

        self::expectException(TargetCallException::class);
        self::expectExceptionMessageMatches('/Invalid action/');

        $handler->handle(new CallContext(Target::fromPathArray([TestService::class, 'nonExistingMethod'])));
    }

    public function testWithoutReflectionWithResolvingFromPath(): void
    {
        $handler = $this->createHandler([
            TestService::class => $service = new TestService(),
        ]);

        self::assertSame(0, $service->counter);

        $handler->handle(new CallContext(Target::fromPathArray([TestService::class, 'increment'])));
        self::assertSame(1, $service->counter);
    }

    public function testUsingResolver(): void
    {
        $handler = $this->createHandler();
        $ctx = new CallContext(
            Target::fromReflection(new \ReflectionFunction(fn (string $value):string => \strtoupper($value)))
        );
        $ctx = $ctx->withArguments(['word' => 'world!', 'value' => 'hello']);

        $result = $handler->handle($ctx);

        self::assertSame('HELLO', $result);
    }

    public function createHandler(array $definitions = [], bool $resolveFromPath = true): ReflectionHandler
    {
        $container = new Container();
        foreach ($definitions as $id => $definition) {
            $container->bind($id, $definition);
        }

        return new ReflectionHandler(
            $container,
            $resolveFromPath,
        );
    }
}
