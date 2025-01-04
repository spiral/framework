<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Container;
use Spiral\Tests\Core\Fixtures\Factory;
use Spiral\Tests\Core\Fixtures\SampleClass;

class BindingsTest extends TestCase
{
    public function testStringBinding(): void
    {
        $container = new Container();
        self::assertInstanceOf(ContainerInterface::class, $container);
        self::assertFalse($container->has('abc'));

        $container->bind('abc', static fn(): string => 'hello');
        $container->bind('dce', 'abc');

        self::assertTrue($container->has('dce'));
        self::assertEquals('hello', $container->get('abc'));
        self::assertEquals($container->get('abc'), $container->get('dce'));
    }

    public function testClassBinding(): void
    {
        $container = new Container();

        self::assertFalse($container->has('sampleClass'));
        $container->bind('sampleClass', SampleClass::class);

        self::assertTrue($container->has('sampleClass'));
        self::assertInstanceOf(SampleClass::class, $container->get('sampleClass'));
    }

    public function testFactoryBinding(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', [Factory::class, 'sampleClass']);
        self::assertInstanceOf(SampleClass::class, $container->get('sampleClass'));
    }

    public function testInstanceBinding(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', new SampleClass());

        $instance = $container->get('sampleClass');

        self::assertInstanceOf(SampleClass::class, $instance);
        self::assertSame($instance, $container->get('sampleClass'));
    }

    public function testAutoScalarBinding(): void
    {
        $container = new Container();
        $container->bind(ConfigsInterface::class, 42.69);

        self::assertSame(42.69, $container->get(ConfigsInterface::class));
    }
}
