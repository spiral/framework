<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Injector;

use PHPUnit\Framework\TestCase;
use Spiral\Attributes\AttributeReader;
use Spiral\Boot\Injector\EnumInjector;
use Spiral\Boot\Injector\ProvideFrom;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Tests\Boot\Fixtures\InjectableEnum;
use Spiral\Tests\Boot\Fixtures\SampleClass;

final class EnumInjectorTest extends TestCase
{
    public function testCreateInjectionForClassWithoutAttribute(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectErrorMessage(
            "Class `Spiral\Tests\Boot\Fixtures\SampleClass` should contain ".
            "`Spiral\Boot\Injector\ProvideFrom` attribute with defined detector method."
        );

        $class = new SampleClass();

        $injector = new EnumInjector(new Container(), new AttributeReader());
        $injector->createInjection(new \ReflectionClass($class));
    }

    public function testCreateInjectionNotForClass(): void
    {
        $class = new #[ProvideFrom(method: 'test')] class {
        };

        $ref = new \ReflectionClass($class);

        $this->expectException(ContainerException::class);
        $this->expectErrorMessage(\sprintf('Class `%s` should be an enum.', $ref->getName()));

        $injector = new EnumInjector(new Container(), new AttributeReader());
        $injector->createInjection($ref);
    }

    public function testCreateInjectionNotForEnum(): void
    {
        $container = new Container();
        $enum = $container->get(InjectableEnum::class);

        $this->assertSame(InjectableEnum::Bar, $enum);
    }
}
