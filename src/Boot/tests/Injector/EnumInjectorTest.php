<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Injector;

use PHPUnit\Framework\TestCase;
use Spiral\Attributes\AttributeReader;
use Spiral\Boot\Injector\EnumInjector;
use Spiral\Boot\Injector\ProvideFrom;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Container\InjectionException;
use Spiral\Tests\Boot\Fixtures\InjectableEnum;
use Spiral\Tests\Boot\Fixtures\InjectableEnumWithNonStaticMethod;
use Spiral\Tests\Boot\Fixtures\InjectableEnumWithoutMethod;
use Spiral\Tests\Boot\Fixtures\SampleClass;

final class EnumInjectorTest extends TestCase
{
    public function testCreateInjectionForClassWithoutAttribute(): void
    {
        $this->expectException(InjectionException::class);
        $this->expectExceptionMessage(
            "Class `Spiral\Tests\Boot\Fixtures\SampleClass` should contain ".
            "`Spiral\Boot\Injector\ProvideFrom` attribute with defined detector method."
        );

        $class = new SampleClass();
        $container = new Container();
        $injector = new EnumInjector($container, $container, new AttributeReader());
        $injector->createInjection(new \ReflectionClass($class));
    }

    public function testCreateInjectionNotForClass(): void
    {
        $class = new #[ProvideFrom(method: 'test')] class {
        };

        $ref = new \ReflectionClass($class);

        $this->expectException(InjectionException::class);
        $this->expectExceptionMessage(\sprintf('Class `%s` should be an enum.', $ref->getName()));

        $container = new Container();
        $injector = new EnumInjector($container, $container, new AttributeReader());
        $injector->createInjection($ref);
    }

    public function testCreateInjectionNotForEnum(): void
    {
        $container = new Container();
        $enum = $container->get(InjectableEnum::class);

        $this->assertSame(InjectableEnum::Bar, $enum);
    }

    public function testCreateInjectionForClassWithoutMethod(): void
    {
        $this->expectException(InjectionException::class);
        $this->expectExceptionMessage(
            "Class `Spiral\Tests\Boot\Fixtures\InjectableEnumWithoutMethod` does not contain `detect` method."
        );

        $container = new Container();
        $container->get(InjectableEnumWithoutMethod::class);
    }

    public function testCreateInjectionForClassWithoutStaticMethod(): void
    {
        $this->expectException(InjectionException::class);
        $this->expectExceptionMessage(
            "Spiral\Tests\Boot\Fixtures\InjectableEnumWithNonStaticMethod::detect` should be static."
        );

        $container = new Container();
        $container->get(InjectableEnumWithNonStaticMethod::class);
    }
}
