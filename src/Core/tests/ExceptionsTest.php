<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\Exception\ConfiguratorException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\LogicException;
use Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency;
use Spiral\Tests\Core\Fixtures\IntersectionTypes;
use Spiral\Tests\Core\Fixtures\InvalidWithContainerInside;
use Spiral\Tests\Core\Fixtures\WithContainerInside;
use Spiral\Tests\Core\Fixtures\WithPrivateConstructor;

class ExceptionsTest extends TestCase
{
    public static function exceptionTraceDataProvider(): \Traversable
    {
        $binding = new Container();
        $binding->bind('Spiral\Tests\Core\Fixtures\InvalidClass', ['invalid', 'invalid']);

        $notConstructed = new Container();
        $notConstructed->bind('Spiral\Tests\Core\Fixtures\InvalidClass', WithPrivateConstructor::class);

        $withClosure = new Container();
        $withClosure->bind('Spiral\Tests\Core\Fixtures\InvalidClass', static fn(): string => 'FooBar');

        $closureWithContainer = new Container();
        $closureWithContainer->bind(
            'Spiral\Tests\Core\Fixtures\InvalidClass',
            static fn(ContainerInterface $container) => $container->get('invalid'),
        );

        yield 'empty container' => [
            new Container(),
            <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`.
            Can't autowire `Spiral\Tests\Core\Fixtures\InvalidClass`: class or injector not found.
            Resolving trace:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              scope: 'root'
              context: null
            - action: 'resolve arguments'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              signature: function (Spiral\Tests\Core\Fixtures\InvalidClass $class)
              - action: 'autowire'
                alias: 'Spiral\Tests\Core\Fixtures\InvalidClass'
                scope: 'root'
                context: Parameter #0 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
            MARKDOWN,
        ];
        yield 'binding' => [
            $binding,
            <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`.
            Can't resolve `Spiral\Tests\Core\Fixtures\InvalidClass`: factory invocation failed.
            Can't autowire `invalid`: class or injector not found.
            Resolving trace:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              scope: 'root'
              context: null
            - action: 'resolve arguments'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              signature: function (Spiral\Tests\Core\Fixtures\InvalidClass $class)
              - action: 'resolve from binding'
                alias: 'Spiral\Tests\Core\Fixtures\InvalidClass'
                scope: 'root'
                context: Parameter #0 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
                binding: Deferred factory 'invalid'->invalid()
                - action: 'autowire'
                  alias: 'invalid'
                  scope: 'root'
                  context: null
            MARKDOWN,
        ];
        yield 'notConstructed' => [
            $notConstructed,
            <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`.
            Can't resolve `Spiral\Tests\Core\Fixtures\InvalidClass`.
            Class `Spiral\Tests\Core\Fixtures\WithPrivateConstructor` can not be constructed.
            Resolving trace:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              scope: 'root'
              context: null
            - action: 'resolve arguments'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              signature: function (Spiral\Tests\Core\Fixtures\InvalidClass $class)
              - action: 'resolve from binding'
                alias: 'Spiral\Tests\Core\Fixtures\InvalidClass'
                scope: 'root'
                context: Parameter #0 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
                binding: Alias to `Spiral\Tests\Core\Fixtures\WithPrivateConstructor`
                - action: 'autowire'
                  alias: 'Spiral\Tests\Core\Fixtures\WithPrivateConstructor'
                  scope: 'root'
                  context: Parameter #0 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
            MARKDOWN,
        ];
        yield 'withClosure' => [
            $withClosure,
            <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`. Invalid argument value type for the `class` parameter when validating arguments for `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency::__construct`.
            Resolving trace:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              scope: 'root'
              context: null
            - action: 'resolve arguments'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              signature: function (Spiral\Tests\Core\Fixtures\InvalidClass $class)
            MARKDOWN,
        ];
        yield 'closureWithContainer' => [
            $closureWithContainer,
            <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`.
            Can't resolve `Spiral\Tests\Core\Fixtures\InvalidClass`: factory invocation failed.
            Can't autowire `invalid`: class or injector not found.
            Resolving trace:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              scope: 'root'
              context: null
            - action: 'resolve arguments'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              signature: function (Spiral\Tests\Core\Fixtures\InvalidClass $class)
              - action: 'resolve from binding'
                alias: 'Spiral\Tests\Core\Fixtures\InvalidClass'
                scope: 'root'
                context: Parameter #0 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
                binding: Factory from static function (Psr\Container\ContainerInterface $container)
                - action: 'autowire'
                  alias: 'invalid'
                  scope: 'root'
                  context: null
            MARKDOWN,
        ];
    }

    public function testInvalidBinding(): void
    {
        $this->expectExceptionMessage('Invalid binding for `invalid`');
        $this->expectException(ConfiguratorException::class);

        $container = new Container();
        $container->bind('invalid', null);
    }

    public function testClone(): void
    {
        $this->expectException(LogicException::class);
        $container = new Container();
        clone $container;
    }

    public function testInvalidInjectionParameter(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            'Can\'t autowire `Spiral\Tests\Core\InvalidClass`: class or injector not found.',
        );

        $container = new Container();

        $container->resolveArguments(new \ReflectionMethod($this, 'invalidInjection'));
    }

    public function testInjectionUsingIntersectionTypes(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Can not resolve unsupported type of the `example` parameter');

        $container = new Container();

        $container->resolveArguments(new \ReflectionMethod(IntersectionTypes::class, 'example'));
    }

    /**
     * Broken dependency in a constructor signature.
     */
    public function testExceptionTraceWithInvalidDependencyInSignature(): void
    {
        $container = new Container();

        $expectedMessage = <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\InvalidWithContainerInside`.
            Can't autowire `Spiral\Tests\Core\Fixtures\InvalidClass`: class or injector not found.
            Resolving trace:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\InvalidWithContainerInside'
              scope: 'root'
              context: null
            - action: 'resolve arguments'
              alias: 'Spiral\Tests\Core\Fixtures\InvalidWithContainerInside'
              signature: function (Psr\Container\ContainerInterface $container, Spiral\Tests\Core\Fixtures\InvalidClass $class)
              - action: 'autowire'
                alias: 'Spiral\Tests\Core\Fixtures\InvalidClass'
                scope: 'root'
                context: Parameter #1 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
            MARKDOWN;

        try {
            $container->get(InvalidWithContainerInside::class);
            self::fail('Exception `ContainerException` expected');
        } catch (ContainerException $e) {
            self::assertSame($expectedMessage, $e->getMessage());
        }
    }

    /**
     * Broken dependency in a constructor body.
     */
    public function testExceptionTraceWithInvalidDependencyInConstructorBody(): void
    {
        $container = new Container();

        self::expectException(ContainerException::class);
        self::expectExceptionMessage("Can't resolve `Spiral\Tests\Core\Fixtures\WithContainerInside`: failed constructing `Spiral\Tests\Core\Fixtures\WithContainerInside`");
        self::expectExceptionMessage("Can't autowire `invalid`: class or injector not found.");

        $container->get(WithContainerInside::class);
    }

    public function testOldTraceShouldBeCleared(): void
    {
        $container = new Container();

        try {
            $container->get('invalid');
        } catch (ContainerException $e) {
            self::assertSame(<<<MARKDOWN
                Can't autowire `invalid`: class or injector not found.
                Resolving trace:
                - action: 'autowire'
                  alias: 'invalid'
                  scope: 'root'
                  context: null
                MARKDOWN, $e->getMessage());
        }

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            <<<MARKDOWN
            Can't autowire `invalid-other`: class or injector not found.
            Resolving trace:
            - action: 'autowire'
              alias: 'invalid-other'
              scope: 'root'
              context: null
            MARKDOWN,
        );

        $container->get('invalid-other');
    }

    #[DataProvider('exceptionTraceDataProvider')]
    public function testExceptionTrace(Container $container, string $message): void
    {
        $this->expectException(ContainerException::class);

        try {
            $container->get(ClassWithUndefinedDependency::class);
        } catch (ContainerException $e) {
            self::assertSame($message, $e->getMessage());

            throw $e;
        }
    }

    protected function invalidInjection(InvalidClass $class): void {}
}
