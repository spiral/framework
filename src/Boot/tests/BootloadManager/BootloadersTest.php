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

final class BootloadersTest extends TestCase
{
    public function testSchemaLoading(): void
    {
        $bootloader = $this->getBootloadManager();

        $bootloader->bootload($classes = [
            SampleClass::class,
            SampleBootWithMethodBoot::class,
            SampleBoot::class,
        ], [
            static function (Container $container, SampleBoot $boot): void {
                $container->bind('efg', $boot);
            },
        ], [
            static function (Container $container, SampleBoot $boot): void {
                $container->bind('ghi', $boot);
            },
        ]);

        self::assertTrue($this->container->has('abc'));
        self::assertTrue($this->container->hasInstance('cde'));
        self::assertTrue($this->container->hasInstance('def'));
        self::assertTrue($this->container->hasInstance('efg'));
        self::assertTrue($this->container->has('single'));
        self::assertTrue($this->container->has('ghi'));
        self::assertNotInstanceOf(SampleBoot::class, $this->container->get('efg'));
        self::assertInstanceOf(SampleBoot::class, $this->container->get('ghi'));

        $classes = \array_filter($classes, static fn(string $class): bool => $class !== SampleClass::class);
        self::assertSame(\array_merge($classes, [
            BootloaderA::class,
            BootloaderB::class,
        ]), $bootloader->getClasses());
    }

    public function testBootloadFromInstance(): void
    {
        $bootloader = $this->getBootloadManager();

        $bootloader->bootload([
            SampleClass::class,
            new SampleBootWithMethodBoot(),
            new SampleBoot(),
        ]);

        self::assertTrue($this->container->has('abc'));
        self::assertTrue($this->container->has('single'));
        self::assertTrue($this->container->hasInstance('def'));
        self::assertTrue($this->container->hasInstance('efg'));
        self::assertTrue($this->container->hasInstance('cde'));
        self::assertTrue($this->container->has('ghi'));

        self::assertSame([
            SampleBootWithMethodBoot::class,
            SampleBoot::class,
            BootloaderA::class,
            BootloaderB::class,
        ], $bootloader->getClasses());
    }

    public function testBootloadFromAnonymousClass(): void
    {
        $bootloader = $this->getBootloadManager();

        $bootloader->bootload([
            new class extends Bootloader {
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

        self::assertTrue($this->container->has('abc'));
        self::assertTrue($this->container->has('single'));
        self::assertTrue($this->container->hasInstance('def'));
        self::assertTrue($this->container->hasInstance('efg'));
        self::assertTrue($this->container->has('ghi'));

        self::assertCount(1, $bootloader->getClasses());
    }

    public function testException(): void
    {
        $this->expectException(\Spiral\Boot\Exception\ClassNotFoundException::class);
        $this->expectExceptionMessage('Bootloader class `Foo\Bar\Invalid` does not exist.');

        $bootloader = $this->getBootloadManager();
        $bootloader->bootload(['Foo\Bar\Invalid']);
    }

    public function testDependenciesFromConstant(): void
    {
        $bootloader = $this->getBootloadManager();
        $bootloader->bootload($classes = [
            SampleBoot::class,
        ]);

        self::assertSame(\array_merge($classes, [
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

        self::assertSame(\array_merge($classes, [
            BootloaderA::class,
        ]), $bootloader->getClasses());
    }

    public function testDependenciesFromInitAndBootMethods(): void
    {
        $bootloader = $this->getBootloadManager();
        $bootloader->bootload($classes = [
            BootloaderC::class,
        ]);

        self::assertSame(\array_merge($classes, [
            BootloaderA::class,
            BootloaderB::class,
        ]), $bootloader->getClasses());
    }
}
