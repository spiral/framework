<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\ArgumentException;
use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\LogicException;
use Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency;
use Spiral\Tests\Core\Fixtures\IntersectionTypes;
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

    public function testExceptionTraceWithContainerInside(): void
    {
        $container = new Container();

        $this->expectException(ContainerException::class);

        try {
            $container->get(WithContainerInside::class);
        } catch (ContainerException $e) {
            $this->assertSame(
                \preg_replace('/\s+/', '', 'Can\'tresolve`Spiral\Tests\Core\Fixtures\
                    WithContainerInside`:undefinedclassorbinding`Spiral\Tests\Core\Fixtures\InvalidClass`.
                    Containerstacktrace:-Spiral\Tests\Core\Fixtures\InvalidClass-Spiral\Core\Container-Psr\Container
                    \ContainerInterface-Spiral\Tests\Core\Fixtures\WithContainerInside'),
                \preg_replace('/\s+/', '', $e->getMessage())
            );

            throw $e;
        }
    }

    /**
     * @dataProvider exceptionTraceDataProvider
     */
    public function testExceptionTrace(Container $container, string $message): void
    {
        $this->expectException(ContainerException::class);

        try {
            $container->get(ClassWithUndefinedDependency::class);
        } catch (ContainerException $e) {
            $this->assertSame(
                \preg_replace('/\s+/', '', $message),
                \preg_replace('/\s+/', '', $e->getMessage())
            );

            throw $e;
        }
    }

    public function exceptionTraceDataProvider(): \Traversable
    {
        $binding = new Container();
        $binding->bind('Spiral\Tests\Core\Fixtures\InvalidClass', ['invalid']);

        $notConstructed = new Container();
        $notConstructed->bind('Spiral\Tests\Core\Fixtures\InvalidClass', WithPrivateConstructor::class);

        yield [
            new Container(),
            'Can\'tresolve`Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency`:undefinedclassorbinding`
            Spiral\Tests\Core\Fixtures\InvalidClass`.Containerstacktrace:-Spiral\Tests\Core\Fixtures\InvalidClass
            -Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
        ];
        yield [
            $binding,
            'Invalidbindingfor`Spiral\Tests\Core\Fixtures\InvalidClass`.Containerstacktrace:-Spiral\Tests\Core\Fixtures\
            InvalidClass-Spiral\Tests\Core\Fixtures\ClassWithUndefinedDependency'
        ];
        yield [
            $notConstructed,
            'Class`Spiral\Tests\Core\Fixtures\WithPrivateConstructor`cannotbeconstructed.Containerstacktrace:-Spiral\
            Tests\Core\Fixtures\WithPrivateConstructor-Spiral\Tests\Core\Fixtures\InvalidClass-Spiral\Tests\Core\
            Fixtures\ClassWithUndefinedDependency'
        ];
    }

    protected function invalidInjection(InvalidClass $class): void
    {
    }
}
