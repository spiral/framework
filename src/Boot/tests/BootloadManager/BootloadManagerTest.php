<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager;

use Spiral\Boot\BootloadManager\BootloadManager;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Boot\BootloadManager\InitializerInterface;
use Spiral\Core\Container;
use Spiral\Tests\Boot\Fixtures\BootloaderA;
use Spiral\Tests\Boot\Fixtures\BootloaderB;
use Spiral\Tests\Boot\Fixtures\SampleBoot;
use Spiral\Tests\Boot\Fixtures\SampleBootWithMethodBoot;
use Spiral\Tests\Boot\Fixtures\SampleClass;
use Spiral\Tests\Boot\TestCase;

final class BootloadManagerTest extends TestCase
{
    public function testWithoutInvokerStrategy(): void
    {
        $container = new Container();
        $container->bind(InitializerInterface::class, new Initializer($container, $container));

        $bootloader = new BootloadManager(
            $container,
            $container,
            $container,
            $container->get(InitializerInterface::class)
        );

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
}
