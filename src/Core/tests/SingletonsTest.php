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
    public function testSingletonInstance(): void
    {
        $container = new Container();
        $container->bindSingleton('sampleClass', $instance = new SampleClass());
        $this->assertSame($instance, $container->get('sampleClass'));
    }

    public function testSingletonToItself(): void
    {
        $container = new Container();
        $container->bindSingleton(SampleClass::class, SampleClass::class);

        $sc = $container->get(SampleClass::class);
        $this->assertTrue($container->hasInstance(SampleClass::class));
        $this->assertSame($sc, $container->get(SampleClass::class));
    }

    public function testSingletonInstanceWithBinding(): void
    {
        $container = new Container();
        $container->bindSingleton('sampleClass', $instance = new SampleClass());
        $container->bind('binding', 'sampleClass');

        $this->assertSame($instance, $container->get('sampleClass'));
        $this->assertSame($instance, $container->get('binding'));
    }

    public function testHasInstance(): void
    {
        $container = new Container();
        $container->bindSingleton('sampleClass', $instance = new SampleClass());

        $this->assertTrue($container->hasInstance('sampleClass'));
        $this->assertFalse($container->hasInstance('otherClass'));
    }

    public function testSingletonClosure(): void
    {
        $container = new Container();

        $instance = new SampleClass();

        $container->bindSingleton('sampleClass', static fn(): SampleClass => $instance);

        $this->assertSame($instance, $container->get('sampleClass'));
    }

    public function testSingletonClosureTwice(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', static fn(): SampleClass => new SampleClass());

        $instance = $container->get('sampleClass');

        $this->assertInstanceOf(SampleClass::class, $instance);
        $this->assertSame($instance, $container->get('sampleClass'));
    }

    public function testSingletonFactory(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', [Factory::class, 'sampleClass']);

        $instance = $container->get('sampleClass');

        $this->assertInstanceOf(SampleClass::class, $instance);
        $this->assertSame($instance, $container->get('sampleClass'));
    }

    #[DataProvider('singletonWithCustomArgsProvider')]
    public function testSingletonWithCustomArgs(string $alias, mixed $definition): void
    {
        $container = new Container();
        $container->bindSingleton($alias, $definition);
        $instance = $container->make($alias);

        $this->assertSame($instance, $container->make($alias));
        $this->assertSame($instance, $container->make($alias, []));
        $this->assertNotSame($instance, $bar = $container->make($alias, ['bar']));
        $this->assertNotSame($bar, $container->make($alias, ['bar']));
        // The binding mustn't be rebound
        $this->assertSame($instance, $container->make($alias));
    }

    public function testSingletonWithCustomArgsObject(): void
    {
        $container = new Container();
        $container->bindSingleton('sampleClass', self::class::sampleClass());
        $instance = $container->make('sampleClass');

        $this->assertSame($instance, $container->make('sampleClass'));
        $this->assertSame($instance, $container->make('sampleClass', []));
        $this->assertNotSame($instance, $bar = $container->make('sampleClass', ['bar']));
        $this->assertNotSame($bar, $container->make('sampleClass', ['bar']));
        // The binding mustn't be rebound
        $this->assertSame($instance, $container->make('sampleClass'));
    }

    public static function singletonWithCustomArgsProvider(): iterable
    {
        static $obj = new \stdClass();
        yield 'array-factory' => ['sampleClass', [Factory::class, 'sampleClass']];
        yield 'class-name' => ['sampleClass', SampleClass::class];
        yield 'reference-existing' => ['stdClass', \WeakReference::create($obj)];
    }

    public function testMakeResultWithCustomArgsWontBeStored(): void
    {
        $container = new Container();
        $instance = $container->make(DeclarativeSingleton::class, ['foo' => 'bar']);

        $this->assertFalse($container->hasInstance(DeclarativeSingleton::class));

        $this->assertNotSame($instance, $container->get(DeclarativeSingleton::class));
    }

    public function testDelayedSingleton(): void
    {
        $container = new Container();
        $container->bindSingleton('singleton', 'sampleClass');

        $container->bind('sampleClass', fn(): SampleClass => new SampleClass());

        $instance = $container->get('singleton');

        $this->assertInstanceOf(SampleClass::class, $instance);
        $this->assertSame($instance, $container->get('singleton'));
        $this->assertNotSame($instance, $container->get('sampleClass'));
    }

    public function testDeclarativeSingleton(): void
    {
        $container = new Container();

        $instance = $container->get(DeclarativeSingleton::class);

        $this->assertInstanceOf(DeclarativeSingleton::class, $instance);
        $this->assertSame($instance, $container->get(DeclarativeSingleton::class));
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

        $this->assertFalse($container->has($class::class));

        $container->get($class::class);

        $this->assertTrue($container->has($class::class));
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
