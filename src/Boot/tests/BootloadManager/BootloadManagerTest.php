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
        $this->container->bind(InitializerInterface::class, new Initializer($this->container, $this->container));

        $bootloader = new BootloadManager(
            $this->container,
            $this->container,
            $this->container,
            $this->container->get(InitializerInterface::class),
        );

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
}
