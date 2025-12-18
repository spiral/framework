<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Container;
use Spiral\Core\Exception\Binder\SingletonOverloadException;
use Spiral\Core\Options;
use Spiral\Tests\Core\Fixtures\Factory;
use Spiral\Tests\Core\Fixtures\SampleClass;

class BindingsTest extends TestCase
{
    public function testHasBinding(): void
    {
        $container = new Container();
        self::assertFalse($container->hasBinding('abc'));

        $container->bind('abc', static fn(): string => 'hello');

        self::assertTrue($container->hasBinding('abc'));
    }

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

    public function testInstanceBindingWithForceMode(): void
    {
        $options = new Options();
        $options->allowSingletonsRebinding = true;
        $container = new Container(options: $options);
        $container->bindSingleton('sampleClass', static fn(): SampleClass => new SampleClass());

        $instance = $container->get('sampleClass');
        $container->bindSingleton('sampleClass', new SampleClass());

        self::assertNotSame($instance, $container->get('sampleClass'));
    }

    public function testInstanceBindingWithForceMode2(): void
    {
        $options = new Options();
        $options->allowSingletonsRebinding = false;
        $container = new Container(options: $options);
        $container->bindSingleton('sampleClass', static fn(): SampleClass => new SampleClass());

        $instance = $container->get('sampleClass');
        $container->bindSingleton('sampleClass', new SampleClass(), true);

        self::assertNotSame($instance, $container->get('sampleClass'));
    }

    public function testInstanceSharedBinding(): void
    {
        $container = new Container();

        $container->bind('sampleClass', new SampleClass());

        self::assertSame($container->get('sampleClass'), $container->get('sampleClass'));
    }

    public function testInstanceBindingWithoutForceMode(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', new SampleClass());

        self::assertSame($container->get('sampleClass'), $container->get('sampleClass'));

        $this->expectException(SingletonOverloadException::class);
        $container->bindSingleton('sampleClass', new SampleClass(), false);
    }

    public function testInstanceBindingWithoutForceMode2(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', static fn(): SampleClass => new SampleClass());

        $container->get('sampleClass');

        $this->expectException(SingletonOverloadException::class);
        $container->bindSingleton('sampleClass', new SampleClass(), false);
    }

    public function testInstanceBindingWithoutForceMode3(): void
    {
        $container = new Container();

        $container->bind('test', new SampleClass());
        $container->bindSingleton('sampleClass', 'test');

        $container->get('sampleClass');

        $this->expectException(SingletonOverloadException::class);
        $container->bindSingleton('sampleClass', new SampleClass(), false);
    }

    public function testInstanceBindingWithoutForceMode4(): void
    {
        $options = new Options();
        $options->allowSingletonsRebinding = false;
        $container = new Container(options: $options);

        $container->bindSingleton('sampleClass', static fn(): SampleClass => new SampleClass());

        $container->get('sampleClass');

        $this->expectException(SingletonOverloadException::class);
        $container->bindSingleton('sampleClass', new SampleClass());
    }

    public function testAutoScalarBinding(): void
    {
        $container = new Container();
        $container->bind(ConfigsInterface::class, 42.69);

        self::assertSame(42.69, $container->get(ConfigsInterface::class));
    }
}
