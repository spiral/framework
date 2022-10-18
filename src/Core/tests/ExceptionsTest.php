<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
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
        $this->expectException(ContainerException::class);
        $container = new Container();
        $container->bind('invalid', ['invalid']);
        $container->get('invalid');
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

    public function testArgumentException(string $param = null): void
    {
        $method = new \ReflectionMethod($this, 'testArgumentException');

        $e = new ArgumentException(
            $method->getParameters()[0],
            $method
        );

        $this->assertInstanceOf(AutowireException::class, $e);
        $this->assertInstanceOf(ContainerException::class, $e);
        $this->assertInstanceOf(ContainerExceptionInterface::class, $e);

        $this->assertSame($method, $e->getContext());
        $this->assertSame('param', $e->getParameter()->getName());
    }

    /**
     * Broken dependency in a constructor signature.
     */
    public function testExceptionTraceWithInvalidDependencyInSignature(): void
    {
        $container = new Container();

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            <<<MARKDOWN
            Can't resolve `Spiral\Tests\Core\Fixtures\InvalidWithContainerInside`: undefined class or binding `Spiral\Tests\Core\Fixtures\InvalidClass`.
            Container trace list:
            - Spiral\Tests\Core\Fixtures\InvalidWithContainerInside
              source: 'autowiring'
              context: NULL
            - Spiral\Tests\Core\Fixtures\InvalidClass
              source: 'autowiring'
              context: 'class'
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
            - Spiral\Tests\Core\Fixtures\WithContainerInside
              source: 'autowiring'
              context: NULL
              - Spiral\Tests\Core\Fixtures\WithContainerInside
                0: 'constructor'
                1: instance of Spiral\Core\Container
              - invalid
                source: 'autowiring'
                context: NULL
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
            $this->assertSame(
                $e->getMessage(),
                <<<MARKDOWN
                Can't resolve `invalid`: undefined class or binding `invalid`.
                Container trace list:
                - invalid
                  source: 'autowiring'
                  context: NULL
                MARKDOWN
            );
        }

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            <<<MARKDOWN
            Can't resolve ``: undefined class or binding `invalid-other`.
            Container trace list:
            - invalid-other
              source: 'autowiring'
              context: NULL
            MARKDOWN
        );

        $container->get('invalid-other');
    }

    /**
     * @dataProvider exceptionTraceDataProvider
     */
    public function testExceptionTrace(Container $container, string $message): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage($message);

        $container->get(ClassWithUndefinedDependency::class);
    }

    public function exceptionTraceDataProvider(): \Traversable
    {
        $binding = new Container();
        $binding->bind('Spiral\Tests\Core\Fixtures\InvalidClass', ['invalid']);

        $notConstructed = new Container();
        $notConstructed->bind('Spiral\Tests\Core\Fixtures\InvalidClass', WithPrivateConstructor::class);

        $withClosure = new Container();
        $withClosure->bind('Spiral\Tests\Core\Fixtures\InvalidClass', static fn() => 'FooBar');

        $closureWithContainer = new Container();
        $closureWithContainer->bind(
            'Spiral\Tests\Core\Fixtures\InvalidClass',
            static fn(ContainerInterface $container) => $container->get('invalid')
        );

        yield [
            new Container(),
            <<<MARKDOWN
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`: undefined class or binding `Spiral\Tests\Core\Fixtures\InvalidClass`.
            Container trace list:
            - Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency
              source: 'autowiring'
              context: NULL
            - Spiral\Tests\Core\Fixtures\InvalidClass
              source: 'autowiring'
              context: 'class'
            MARKDOWN
        ];
        yield [
            $binding,
            <<<MARKDOWN
            Invalid binding for `Spiral\Tests\Core\Fixtures\InvalidClass`.
            Container trace list:
            - Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency
              source: 'autowiring'
              context: NULL
            - Spiral\Tests\Core\Fixtures\InvalidClass
              source: 'binding'
              binding: array
              context: 'class'
            MARKDOWN
        ];
        yield [
            $notConstructed,
            <<<MARKDOWN
            Class `Spiral\Tests\Core\Fixtures\WithPrivateConstructor` can not be constructed.
            Container trace list:
            - Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency
              source: 'autowiring'
              context: NULL
            - Spiral\Tests\Core\Fixtures\InvalidClass
              source: 'binding'
              binding: 'Spiral\Tests\Core\Fixtures\WithPrivateConstructor'
              context: 'class'
            - Spiral\Tests\Core\Fixtures\WithPrivateConstructor
              source: 'autowiring'
              context: 'class'
            MARKDOWN
        ];
        yield [
            $withClosure,
            <<<MARKDOWN
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`. Invalid argument value type for the `class` parameter when validating arguments for `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency::__construct`.
            Container trace list:
            - Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency
              source: 'autowiring'
              context: NULL
            MARKDOWN
        ];
        yield [
            $closureWithContainer,
            <<<MARKDOWN
            Can't resolve `Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`: undefined class or binding `invalid`.
            Container trace list:
            - Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency
              source: 'autowiring'
              context: NULL
            - Spiral\Tests\Core\Fixtures\InvalidClass
              source: 'binding'
              binding: array
              context: 'class'
            - invalid
              source: 'autowiring'
              context: NULL
            MARKDOWN
        ];
    }

    protected function invalidInjection(InvalidClass $class): void
    {
    }
}
