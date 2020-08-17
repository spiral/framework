<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Tests\Core\Fixtures\DeclarativeSingleton;
use Spiral\Tests\Core\Fixtures\SampleClass;

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

        $container->bindSingleton('sampleClass', function () use ($instance) {
            return $instance;
        });

        $this->assertSame($instance, $container->get('sampleClass'));
    }

    public function testSingletonClosureTwice(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', function () {
            return new SampleClass();
        });

        $instance = $container->get('sampleClass');

        $this->assertInstanceOf(SampleClass::class, $instance);
        $this->assertSame($instance, $container->get('sampleClass'));
    }

    public function testSingletonFactory(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', [self::class, 'sampleClass']);

        $instance = $container->get('sampleClass');

        $this->assertInstanceOf(SampleClass::class, $instance);
        $this->assertSame($instance, $container->get('sampleClass'));
    }

    public function testDelayedSingleton(): void
    {
        $container = new Container();
        $container->bindSingleton('singleton', 'sampleClass');

        $container->bind('sampleClass', function () {
            return new SampleClass();
        });

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

    /**
     * @return SampleClass
     */
    private function sampleClass()
    {
        return new SampleClass();
    }
}
