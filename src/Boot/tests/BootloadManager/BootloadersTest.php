<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container;
use Spiral\Tests\Boot\Fixtures\BootloaderA;
use Spiral\Tests\Boot\Fixtures\BootloaderB;
use Spiral\Tests\Boot\Fixtures\BootloaderC;
use Spiral\Tests\Boot\Fixtures\SampleBoot;
use Spiral\Tests\Boot\Fixtures\SampleBootWithMethodBoot;
use Spiral\Tests\Boot\Fixtures\SampleClass;
use Spiral\Tests\Boot\TestCase;

class BootloadersTest extends TestCase
{
    public function testSchemaLoading(): void
    {
        $container = new Container();

        $bootloader = $this->getBootloadManager($container);

        $bootloader->bootload($classes = [
            SampleClass::class,
            SampleBootWithMethodBoot::class,
            SampleBoot::class,
        ], [
            static function(Container $container, SampleBoot $boot) {
                $container->bind('efg', $boot);
            }
        ], [
            static function(Container $container, SampleBoot $boot) {
                $container->bind('ghi', $boot);
            }
        ]);

        $this->assertTrue($container->has('abc'));
        $this->assertTrue($container->hasInstance('cde'));
        $this->assertTrue($container->hasInstance('def'));
        $this->assertTrue($container->hasInstance('efg'));
        $this->assertTrue($container->has('single'));
        $this->assertTrue($container->has('ghi'));
        $this->assertNotInstanceOf(SampleBoot::class, $container->get('efg'));
        $this->assertInstanceOf(SampleBoot::class, $container->get('ghi'));

        $this->assertSame(\array_merge($classes, [
            BootloaderA::class,
            BootloaderB::class,
        ]), $bootloader->getClasses());
    }

    public function testBootloadFromInstance(): void
    {
        $container = new Container();

        $bootloader = $this->getBootloadManager($container);

        $bootloader->bootload([
            SampleClass::class,
            new SampleBootWithMethodBoot(),
            new SampleBoot(),
        ]);

        $this->assertTrue($container->has('abc'));
        $this->assertTrue($container->has('single'));
        $this->assertTrue($container->hasInstance('def'));
        $this->assertTrue($container->hasInstance('efg'));
        $this->assertTrue($container->hasInstance('cde'));
        $this->assertTrue($container->has('ghi'));

        $this->assertSame([
            SampleClass::class,
            SampleBootWithMethodBoot::class,
            SampleBoot::class,
            BootloaderA::class,
            BootloaderB::class,
        ], $bootloader->getClasses());
    }

    public function testBootloadFromAnonymousClass(): void
    {
        $container = new Container();

        $bootloader = $this->getBootloadManager($container);

        $bootloader->bootload([
            new class () extends Bootloader {
                public const BINDINGS = ['abc' => self::class];
                public const SINGLETONS = ['single' => self::class];

                public function init(BinderInterface $binder): void
                {
                    $binder->bind('def', new SampleBoot());
                }

                public function boot(BinderInterface $binder): void
                {
                    $binder->bind('efg', new SampleClass());
                    $binder->bind('ghi', 'foo');
                }
            },
        ]);

        $this->assertTrue($container->has('abc'));
        $this->assertTrue($container->has('single'));
        $this->assertTrue($container->hasInstance('def'));
        $this->assertTrue($container->hasInstance('efg'));
        $this->assertTrue($container->has('ghi'));

        $this->assertCount(1, $bootloader->getClasses());
    }

    public function testException(): void
    {
        $this->expectException(\Spiral\Boot\Exception\ClassNotFoundException::class);
        $this->expectErrorMessage('Bootloader class `Foo\Bar\Invalid` is not exist.');

        $bootloader = $this->getBootloadManager();
        $bootloader->bootload(['Foo\Bar\Invalid']);
    }

    public function testDependenciesFromConstant(): void
    {
        $bootloader = $this->getBootloadManager();
        $bootloader->bootload($classes = [
            SampleBoot::class,
        ]);

        $this->assertSame(\array_merge($classes, [
            BootloaderA::class,
            BootloaderB::class,
        ]), $bootloader->getClasses());
    }

    public function testDependenciesFromInterfaceMethod(): void
    {
        $bootloader = $this->getBootloadManager();
        $bootloader->bootload($classes = [
            BootloaderB::class,
        ]);

        $this->assertSame(\array_merge($classes, [
            BootloaderA::class,
        ]), $bootloader->getClasses());
    }

    public function testDependenciesFromInitAndBootMethods(): void
    {
        $bootloader = $this->getBootloadManager();
        $bootloader->bootload($classes = [
            BootloaderC::class,
        ]);

        $this->assertSame(\array_merge($classes, [
            BootloaderA::class,
            BootloaderB::class
        ]), $bootloader->getClasses());
    }
}
