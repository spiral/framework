<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\Exception\ConfiguratorException;
use Spiral\Core\Exception\Container\ArgumentException;
use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\LogicException;
use Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency;
use Spiral\Tests\Core\Fixtures\IntersectionTypes;
use Spiral\Tests\Core\Fixtures\InvalidWithContainerInside;
use Spiral\Tests\Core\Fixtures\WithContainerInside;
use Spiral\Tests\Core\Fixtures\WithPrivateConstructor;

class ExceptionsTest extends TestCase
{
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
            'Can\'t resolve `Spiral\Tests\Core\InvalidClass`: undefined class or binding `Spiral\Tests\Core\InvalidClass`.'
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

    public function testArgumentException(?string $param = null): void
    {
        $method = new \ReflectionMethod($this, 'testArgumentException');

        $e = new ArgumentException(
            $method->getParameters()[0],
            $method
        );

        self::assertInstanceOf(AutowireException::class, $e);
        self::assertInstanceOf(ContainerException::class, $e);
        self::assertInstanceOf(ContainerExceptionInterface::class, $e);

        self::assertSame($method, $e->getContext());
        self::assertSame('param', $e->getParameter()->getName());
    }

    /**
     * Broken dependency in a constructor signature.
     */
    public function testExceptionTraceWithInvalidDependencyInSignature(): void
    {
        $container = new Container();

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\InvalidWithContainerInside`: undefined class or binding `Spiral\Tests\Core\Fixtures\InvalidClass`.
            Container trace list:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\InvalidWithContainerInside'
              context: null
            - action: 'resolve arguments'
              signature: function (Psr\Container\ContainerInterface $container, Spiral\Tests\Core\Fixtures\InvalidClass $class)
              - action: 'autowire'
                alias: 'Spiral\Tests\Core\Fixtures\InvalidClass'
                context: Parameter #1 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
            MARKDOWN,
        );

        $container->get(InvalidWithContainerInside::class);
    }

    /**
     * Broken dependency in a constructor body.
     */
    public function testExceptionTraceWithInvalidDependencyInConstructorBody(): void
    {
        $container = new Container();

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            <<<MARKDOWN
            Can't resolve `Spiral\Tests\Core\Fixtures\WithContainerInside`: undefined class or binding `invalid`.
            Container trace list:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\WithContainerInside'
              context: null
            - call: 'Spiral\Tests\Core\Fixtures\WithContainerInside::__construct'
              arguments: [
                0: instance of Spiral\Core\Container
              ]
              - action: 'autowire'
                alias: 'invalid'
                context: null
            MARKDOWN,
        );

        $container->get(WithContainerInside::class);
    }

    public function testOldTraceShouldBeCleared(): void
    {
        $container = new Container();

        try {
            $container->get('invalid');
        } catch (ContainerException $e) {
            self::assertSame(<<<MARKDOWN
                Can't resolve `invalid`: undefined class or binding `invalid`.
                Container trace list:
                - action: 'autowire'
                  alias: 'invalid'
                  context: null
                MARKDOWN, $e->getMessage());
        }

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            <<<MARKDOWN
            Can't resolve `invalid-other`: undefined class or binding `invalid-other`.
            Container trace list:
            - action: 'autowire'
              alias: 'invalid-other'
              context: null
            MARKDOWN
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
            static fn(ContainerInterface $container) => $container->get('invalid')
        );

        yield 'empty container' => [
            new Container(),
            <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`: undefined class or binding `Spiral\Tests\Core\Fixtures\InvalidClass`.
            Container trace list:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              context: null
            - action: 'resolve arguments'
              signature: function (Spiral\Tests\Core\Fixtures\InvalidClass $class)
              - action: 'autowire'
                alias: 'Spiral\Tests\Core\Fixtures\InvalidClass'
                context: Parameter #0 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
            MARKDOWN
        ];
        yield 'binding' => [
            $binding,
            <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`: undefined class or binding `invalid`.
            Container trace list:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              context: null
            - action: 'resolve arguments'
              signature: function (Spiral\Tests\Core\Fixtures\InvalidClass $class)
              - action: 'resolve from binding'
                alias: 'Spiral\Tests\Core\Fixtures\InvalidClass'
                scope: 'root'
                context: Parameter #0 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
                binding: Deferred factory 'invalid'->invalid()
                - action: 'autowire'
                  alias: 'invalid'
                  context: null
            MARKDOWN
        ];
        yield 'notConstructed' => [
            $notConstructed,
            <<<'MARKDOWN'
            Class `Spiral\Tests\Core\Fixtures\WithPrivateConstructor` can not be constructed.
            Container trace list:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              context: null
            - action: 'resolve arguments'
              signature: function (Spiral\Tests\Core\Fixtures\InvalidClass $class)
              - action: 'resolve from binding'
                alias: 'Spiral\Tests\Core\Fixtures\InvalidClass'
                scope: 'root'
                context: Parameter #0 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
                binding: Alias to `Spiral\Tests\Core\Fixtures\WithPrivateConstructor`
                - action: 'autowire'
                  alias: 'Spiral\Tests\Core\Fixtures\WithPrivateConstructor'
                  context: Parameter #0 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
            MARKDOWN
        ];
        yield 'withClosure' => [
            $withClosure,
            <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`. Invalid argument value type for the `class` parameter when validating arguments for `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency::__construct`.
            Container trace list:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              context: null
            - action: 'resolve arguments'
              signature: function (Spiral\Tests\Core\Fixtures\InvalidClass $class)
            MARKDOWN
        ];
        yield 'closureWithContainer' => [
            $closureWithContainer,
            <<<'MARKDOWN'
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`: undefined class or binding `invalid`.
            Container trace list:
            - action: 'autowire'
              alias: 'Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
              context: null
            - action: 'resolve arguments'
              signature: function (Spiral\Tests\Core\Fixtures\InvalidClass $class)
              - action: 'resolve from binding'
                alias: 'Spiral\Tests\Core\Fixtures\InvalidClass'
                scope: 'root'
                context: Parameter #0 [ <required> Spiral\Tests\Core\Fixtures\InvalidClass $class ]
                binding: Factory from static function (Psr\Container\ContainerInterface $container)
                - action: 'autowire'
                  alias: 'invalid'
                  context: null
            MARKDOWN
        ];
    }

    protected function invalidInjection(InvalidClass $class): void
    {
    }
}
