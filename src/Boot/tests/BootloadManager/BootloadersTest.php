<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager\BootloadManager;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Core\Container;
use Spiral\Tests\Boot\Fixtures\BootloaderA;
use Spiral\Tests\Boot\Fixtures\BootloaderB;
use Spiral\Tests\Boot\Fixtures\BootloaderC;
use Spiral\Tests\Boot\Fixtures\SampleBoot;
use Spiral\Tests\Boot\Fixtures\SampleBootWithMethodBoot;
use Spiral\Tests\Boot\Fixtures\SampleClass;

class BootloadersTest extends TestCase
{
    public function testSchemaLoading(): void
    {
        $container = new Container();

        $bootloader = new BootloadManager($container, new Initializer($container));
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

    public function testException(): void
    {
        $this->expectException(\Spiral\Boot\Exception\ClassNotFoundException::class);
        $this->expectErrorMessage('Bootloader class `Foo\Bar\Invalid` is not exist.');

        $container = new Container();

        $bootloader = new BootloadManager($container, new Initializer($container));
        $bootloader->bootload(['Foo\Bar\Invalid']);
    }

    public function testDependenciesFromConstant(): void
    {
        $container = new Container();

        $bootloader = new BootloadManager($container, new Initializer($container));
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
        $container = new Container();

        $bootloader = new BootloadManager($container, new Initializer($container));
        $bootloader->bootload($classes = [
            BootloaderB::class,
        ]);

        $this->assertSame(\array_merge($classes, [
            BootloaderA::class,
        ]), $bootloader->getClasses());
    }

    public function testDependenciesFromInitAndBootMethods(): void
    {
        $container = new Container();

        $bootloader = new BootloadManager($container, new Initializer($container));
        $bootloader->bootload($classes = [
            BootloaderC::class,
        ]);

        $this->assertSame(\array_merge($classes, [
            BootloaderA::class,
            BootloaderB::class
        ]), $bootloader->getClasses());
    }
}
