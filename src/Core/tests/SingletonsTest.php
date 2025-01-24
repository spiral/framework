<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Container;
use Spiral\Tests\Core\Fixtures\DeclarativeSingleton;
use Spiral\Tests\Core\Fixtures\Factory;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Fixtures\SingletonAttribute;

class SingletonsTest extends TestCase
{
    public static function singletonWithCustomArgsProvider(): iterable
    {
        static $obj = new \stdClass();
        yield 'array-factory' => ['sampleClass', [Factory::class, 'sampleClass']];
        yield 'class-name' => ['sampleClass', SampleClass::class];
        yield 'reference-existing' => ['stdClass', \WeakReference::create($obj)];
    }

    public function testSingletonInstance(): void
    {
        $container = new Container();
        $container->bindSingleton('sampleClass', $instance = new SampleClass());
        self::assertSame($instance, $container->get('sampleClass'));
    }

    public function testSingletonToItself(): void
    {
        $container = new Container();
        $container->bindSingleton(SampleClass::class, SampleClass::class);

        $sc = $container->get(SampleClass::class);
        self::assertTrue($container->hasInstance(SampleClass::class));
        self::assertSame($sc, $container->get(SampleClass::class));
    }

    public function testSingletonInstanceWithBinding(): void
    {
        $container = new Container();
        $container->bindSingleton('sampleClass', $instance = new SampleClass());
        $container->bind('binding', 'sampleClass');

        self::assertSame($instance, $container->get('sampleClass'));
        self::assertSame($instance, $container->get('binding'));
    }

    public function testHasInstance(): void
    {
        $container = new Container();
        $container->bindSingleton('sampleClass', $instance = new SampleClass());

        self::assertTrue($container->hasInstance('sampleClass'));
        self::assertFalse($container->hasInstance('otherClass'));
    }

    public function testSingletonClosure(): void
    {
        $container = new Container();

        $instance = new SampleClass();

        $container->bindSingleton('sampleClass', static fn(): SampleClass => $instance);

        self::assertSame($instance, $container->get('sampleClass'));
    }

    public function testSingletonClosureTwice(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', static fn(): SampleClass => new SampleClass());

        $instance = $container->get('sampleClass');

        self::assertInstanceOf(SampleClass::class, $instance);
        self::assertSame($instance, $container->get('sampleClass'));
    }

    public function testSingletonFactory(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', [Factory::class, 'sampleClass']);

        $instance = $container->get('sampleClass');

        self::assertInstanceOf(SampleClass::class, $instance);
        self::assertSame($instance, $container->get('sampleClass'));
    }

    #[DataProvider('singletonWithCustomArgsProvider')]
    public function testSingletonWithCustomArgs(string $alias, mixed $definition): void
    {
        $container = new Container();
        $container->bindSingleton($alias, $definition);
        $instance = $container->make($alias);

        self::assertSame($instance, $container->make($alias));
        self::assertSame($instance, $container->make($alias, []));
        self::assertNotSame($instance, $bar = $container->make($alias, ['bar']));
        self::assertNotSame($bar, $container->make($alias, ['bar']));
        // The binding mustn't be rebound
        self::assertSame($instance, $container->make($alias));
    }

    public function testSingletonWithCustomArgsObject(): void
    {
        $container = new Container();
        $container->bindSingleton('sampleClass', self::class::sampleClass());
        $instance = $container->make('sampleClass');

        self::assertSame($instance, $container->make('sampleClass'));
        self::assertSame($instance, $container->make('sampleClass', []));
        self::assertNotSame($instance, $bar = $container->make('sampleClass', ['bar']));
        self::assertNotSame($bar, $container->make('sampleClass', ['bar']));
        // The binding mustn't be rebound
        self::assertSame($instance, $container->make('sampleClass'));
    }

    public function testMakeResultWithCustomArgsWontBeStored(): void
    {
        $container = new Container();
        $instance = $container->make(DeclarativeSingleton::class, ['foo' => 'bar']);

        self::assertFalse($container->hasInstance(DeclarativeSingleton::class));

        self::assertNotSame($instance, $container->get(DeclarativeSingleton::class));
    }

    public function testDelayedSingleton(): void
    {
        $container = new Container();
        $container->bindSingleton('singleton', 'sampleClass');

        $container->bind('sampleClass', static fn(): SampleClass => new SampleClass());

        $instance = $container->get('singleton');

        self::assertInstanceOf(SampleClass::class, $instance);
        self::assertSame($instance, $container->get('singleton'));
        self::assertNotSame($instance, $container->get('sampleClass'));
    }

    public function testDeclarativeSingleton(): void
    {
        $container = new Container();

        $instance = $container->get(DeclarativeSingleton::class);

        self::assertInstanceOf(DeclarativeSingleton::class, $instance);
        self::assertSame($instance, $container->get(DeclarativeSingleton::class));
    }

    public function testAttribute(): void
    {
        $container = new Container();

        $first = $container->get(SingletonAttribute::class);
        $second = $container->get(SingletonAttribute::class);

        self::assertSame($first, $second);
    }

    public function testAttributeAnonClass(): void
    {
        $container = new Container();
        $container->bind('foo', $this->makeAttributedClass(...));
        $first = $container->get('foo');
        $second = $container->get('foo');

        self::assertSame($first, $second);
    }

    public function testHasShouldReturnTrueWhenSingletonIsAlreadyConstructed(): void
    {
        $container = new Container();
        $class = new #[Singleton] class {};

        self::assertFalse($container->has($class::class));

        $container->get($class::class);

        self::assertTrue($container->has($class::class));
    }

    private function makeAttributedClass(): object
    {
        return new
        #[Singleton]
        class {
            public string $baz = 'baz';
        };
    }

    /**
     * @return SampleClass
     */
    private function sampleClass()
    {
        return new SampleClass();
    }
}
