<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Spiral\Core\Config\Injectable;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\InjectionException;
use Spiral\Tests\Core\Fixtures\ExtendedContextInjector;
use Spiral\Tests\Core\Fixtures\InjectableClassChildImplementation;
use Spiral\Tests\Core\Fixtures\InjectableClassChildInterface;
use Spiral\Tests\Core\Fixtures\InjectableClassInterface;
use Spiral\Tests\Core\Fixtures\InjectableClassImplementation;
use Spiral\Tests\Core\Fixtures\InvalidInjector;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Fixtures\TestConfig;
use stdClass;

class InjectableTest extends TestCase
{
    public function testMissingInjector(): void
    {
        $this->expectExceptionMessage(
            "Can't resolve `Spiral\Tests\Core\Fixtures\TestConfig`: undefined class or binding `Spiral\Core\ConfigsInterface`."
        );
        $this->expectException(AutowireException::class);

        $container = new Container();
        $container->get(TestConfig::class);
    }

    public function testInvalidInjector(): void
    {
        $excepted = "Class 'Spiral\Tests\Core\Fixtures\InvalidInjector' must be an " .
                    "instance of InjectorInterface for 'Spiral\Tests\Core\Fixtures\TestConfig'";
        $this->expectException(InjectionException::class);
        $this->expectExceptionMessage($excepted);

        $container = new Container();

        $container->bindInjector(TestConfig::class, InvalidInjector::class);
        $container->get(TestConfig::class);
    }

    public function testInvalidInjectorBinding(): void
    {
        $this->expectException(AutowireException::class);
        $this->expectExceptionMessage(
            "Can't resolve `Spiral\Tests\Core\Fixtures\TestConfig`: undefined class or binding `invalid-injector`."
        );

        $container = new Container();

        $container->bindInjector(TestConfig::class, 'invalid-injector');
        $container->get(TestConfig::class);
    }

    public function testInvalidRuntimeInjector(): void
    {
        $excepted = "Class 'Spiral\Tests\Core\Fixtures\InvalidInjector' must be an " .
            "instance of InjectorInterface for 'Spiral\Tests\Core\Fixtures\TestConfig'";
        $this->expectException(InjectionException::class);
        $this->expectExceptionMessage($excepted);

        $container = new Container();

        $container->bindInjector(TestConfig::class, 'invalid-injector');
        $container->bind('invalid-injector', new InvalidInjector());

        $container->get(TestConfig::class);
    }

    public function testInvalidInjection(): void
    {
        $this->expectException(InjectionException::class);
        $this->expectExceptionMessage("Invalid injection response for 'Spiral\Tests\Core\Fixtures\TestConfig'");

        $container = new Container();

        $configurator = m::mock(ConfigsInterface::class);
        $container->bind(ConfigsInterface::class, $configurator);

        $configurator->shouldReceive('createInjection')->andReturn(new SampleClass());

        $container->get(TestConfig::class);
    }

    public function testInjector(): void
    {
        $configurator = m::mock(ConfigsInterface::class);
        $expected = new TestConfig();

        $container = new Container();
        $container->bind(ConfigsInterface::class, $configurator);

        $configurator->shouldReceive('createInjection')
            ->with(m::on(static fn(ReflectionClass $r): bool => $r->getName() === TestConfig::class), null)
            ->andReturn($expected);

        $this->assertSame($expected, $container->get(TestConfig::class));
    }

    public function testInjectorWithContext(): void
    {
        $configurator = m::mock(ConfigsInterface::class);
        $expected = new TestConfig();

        $container = new Container();
        $container->bind(ConfigsInterface::class, $configurator);

        $configurator->shouldReceive('createInjection')
            ->with(m::on(static fn(ReflectionClass $r): bool => $r->getName() === TestConfig::class), 'context')
            ->andReturn($expected);

        $this->assertSame($expected, $container->get(TestConfig::class, 'context'));
    }

    public function testInjectorForMethod(): void
    {
        $configurator = m::mock(ConfigsInterface::class);
        $expected = new TestConfig();

        $container = new Container();
        $container->bind(ConfigsInterface::class, $configurator);

        $configurator->shouldReceive('createInjection')
            ->with(
                m::on(static fn(ReflectionClass $r): bool => $r->getName() === TestConfig::class),
                'contextArgument'
            )
            ->andReturn($expected);

        $arguments = $container->resolveArguments(new ReflectionMethod(...[$this, 'methodInjection']));
        $this->assertCount(1, $arguments);
        $this->assertSame($expected, $arguments[0]);
    }

    public function testCheckIsClassHasInjector(): void
    {
        $configurator = m::mock(ConfigsInterface::class);

        $container = new Container();
        $container->bind(ConfigsInterface::class, $configurator);
        $container->bindInjector(InjectableClassInterface::class, 'bar');

        $this->assertFalse($container->hasInjector(SampleClass::class));

        $this->assertTrue($container->hasInjector(TestConfig::class));
        $this->assertTrue($container->hasInjector(InjectableClassInterface::class));
        $this->assertTrue($container->hasInjector(InjectableClassImplementation::class));
    }

    public static function inheritanceDataProvider(): iterable
    {
        yield 'parent' => [InjectableClassInterface::class];
        yield 'child' => [InjectableClassChildInterface::class];
        yield 'parent-impl' => [InjectableClassImplementation::class];
        yield 'child-impl' => [InjectableClassChildImplementation::class];
    }

    #[DataProvider('inheritanceDataProvider')]
    public function testInjectableInheritance(string $class): void
    {
        $mock = $this->createMock(Container\InjectorInterface::class);
        $mock->expects($this->once())
            ->method('createInjection')
            ->with(
                // Class
                $this->callback(
                    static fn(ReflectionClass $r): bool => $r->getName() === $class
                ),
                // Context
                null,
            )
            ->willReturn($this->createMock($class));

        $container = new Container();
        $container->bind('injector', $mock);
        $container->bindInjector(InjectableClassInterface::class, 'injector');

        $container->get($class);
    }

    public function testExtendedInjector(): void
    {
        $container = new Container();
        $container->bindInjector(stdClass::class, ExtendedContextInjector::class);

        $result = $container->invoke(static fn(stdClass $dt): \stdClass => $dt);

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertInstanceOf(\ReflectionParameter::class, $result->context);
    }

    public function testExtendedInjectorAnonClassObjectParam(): void
    {
        $container = new Container();
        $container->bind(stdClass::class, new Injectable(new class implements Container\InjectorInterface {
            public function createInjection(\ReflectionClass $class, object|string|null $context = null): object
            {
                return (object)['context' => $context];
            }
        }));

        $result = $container->invoke(static fn(stdClass $dt): \stdClass => $dt);

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertInstanceOf(\ReflectionParameter::class, $result->context);
    }

    public function testExtendedInjectorAnonClassMixedParam(): void
    {
        $container = new Container();
        $container->bind(stdClass::class, new Injectable(new class implements Container\InjectorInterface {
            public function createInjection(\ReflectionClass $class, mixed $context = null): object
            {
                return (object)['context' => $context];
            }
        }));

        $result = $container->invoke(static fn(stdClass $dt): \stdClass => $dt);

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertInstanceOf(\ReflectionParameter::class, $result->context);
    }

    private function methodInjection(TestConfig $contextArgument): void
    {
    }
}
