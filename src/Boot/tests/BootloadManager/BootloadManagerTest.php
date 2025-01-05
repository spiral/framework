<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Boot\BootloadManager\AttributeResolver;
use Spiral\Boot\BootloadManager\BootloadManager;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Boot\BootloadManager\InitializerInterface;
use Spiral\Boot\BootloadManagerInterface;
use Spiral\Core\Container;
use Spiral\Tests\Boot\Fixtures\BootloaderA;
use Spiral\Tests\Boot\Fixtures\BootloaderB;
use Spiral\Tests\Boot\Fixtures\BootloaderL;
use Spiral\Tests\Boot\Fixtures\BootloaderM;
use Spiral\Tests\Boot\Fixtures\BootloaderO;
use Spiral\Tests\Boot\Fixtures\BootloaderP;
use Spiral\Tests\Boot\Fixtures\BootloaderQ;
use Spiral\Tests\Boot\Fixtures\BootloaderR;
use Spiral\Tests\Boot\Fixtures\BootloaderS;
use Spiral\Tests\Boot\Fixtures\SampleBoot;
use Spiral\Tests\Boot\Fixtures\SampleBootWithMethodBoot;
use Spiral\Tests\Boot\Fixtures\SampleClass;
use Spiral\Tests\Boot\Fixtures\SampleClass2;
use Spiral\Tests\Boot\Fixtures\SampleClass3;
use Spiral\Tests\Boot\Fixtures\SampleClassInterface;
use Spiral\Tests\Boot\Fixtures\SampleInjectableClass;
use Spiral\Tests\Boot\TestCase;

final class BootloadManagerTest extends TestCase
{
    public function testWithoutInvokerStrategy(): void
    {
        $bootloader = $this->getBootloadManager();
        $bootloader->bootload(
            $classes = [
                SampleClass::class,
                SampleBootWithMethodBoot::class,
                SampleBoot::class,
            ],
            [
                static function (Container $container, SampleBoot $boot): void {
                    $container->bind('efg', $boot);
                },
            ],
            [
                static function (Container $container, SampleBoot $boot): void {
                    $container->bind('ghi', $boot);
                },
            ],
        );

        $this->assertTrue($this->container->has('abc'));
        $this->assertTrue($this->container->hasInstance('cde'));
        $this->assertTrue($this->container->hasInstance('def'));
        $this->assertTrue($this->container->hasInstance('efg'));
        $this->assertTrue($this->container->has('single'));
        $this->assertTrue($this->container->has('ghi'));
        $this->assertTrue($this->container->has(SampleClassInterface::class));
        $this->assertTrue($this->container->has(SampleClass::class));
        $this->assertTrue($this->container->has(SampleClass2::class));
        $this->assertTrue($this->container->has(SampleClass3::class));

        $this->assertTrue($this->container->hasInjector(SampleInjectableClass::class));
        $this->assertInstanceOf(
            SampleInjectableClass::class,
            $injectable = $this->container->get(SampleInjectableClass::class),
        );
        $this->assertSame('foo', $injectable->name);

        $this->assertNotInstanceOf(SampleBoot::class, $this->container->get('efg'));
        $this->assertInstanceOf(SampleBoot::class, $this->container->get('ghi'));

        $this->assertNotSame($this->container->get(SampleClass2::class), $this->container->get(SampleClass2::class));
        $this->assertSame($this->container->get(SampleClass3::class), $this->container->get(SampleClass3::class));

        $classes = \array_filter($classes, static fn(string $class): bool => $class !== SampleClass::class);
        $this->assertSame(\array_merge($classes, [
            BootloaderA::class,
            BootloaderB::class,
        ]), $bootloader->getClasses());
    }

    public function testSingletonAliases(): void
    {
        $bootloader = $this->getBootloadManager();

        $bootloader->bootload([BootloaderQ::class]);

        $this->assertTrue($this->container->has(SampleClass::class));
        $this->assertTrue($this->container->has(SampleClassInterface::class));

        $this->assertSame(
            $this->container->get(SampleClass::class),
            $this->container->get(SampleClassInterface::class),
        );
    }

    public function testBindingAliases(): void
    {
        $bootloader = $this->getBootloadManager();

        $bootloader->bootload([BootloaderR::class]);

        $this->assertTrue($this->container->has(SampleClass::class));
        $this->assertTrue($this->container->has(SampleClassInterface::class));

        $this->assertNotSame(
            $this->container->get(SampleClass::class),
            $this->container->get(SampleClassInterface::class),
        );
    }

    public function testBindingAliases2(): void
    {
        $bootloader = $this->getBootloadManager();

        $bootloader->bootload([BootloaderS::class]);

        $this->assertTrue($this->container->has('sample1'));
        $this->assertTrue($this->container->has('sample2'));
        $this->assertTrue($this->container->has('sample3'));
        $this->assertFalse($this->container->has(SampleClass::class));
        $this->assertFalse($this->container->has(SampleClassInterface::class));

        $this->assertTrue($this->container->has('sample4'));
        $this->assertTrue($this->container->has('sample5'));
        $this->assertTrue($this->container->has('sample6'));
        $this->assertTrue($this->container->has('sample7'));
        $this->assertTrue($this->container->has(SampleClass2::class));
    }

    #[DataProvider('provideErrorBootloader')]
    public function testErrorAttributes(string $bootloaderClass): void
    {
        $bootloader = $this->getBootloadManager();

        $this->expectException(\LogicException::class);

        $bootloader->bootload([$bootloaderClass]);
    }

    public static function provideErrorBootloader(): iterable
    {
        yield [BootloaderL::class];
        yield [BootloaderM::class];
        yield [BootloaderO::class];
        yield [BootloaderP::class];
    }
}
