<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Bootloader;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\Bootloader\BootloaderRegistry;

final class BootloaderRegistryTest extends TestCase
{
    public function testConstructor(): void
    {
        $registry = new BootloaderRegistry();
        self::assertSame([], $registry->getSystemBootloaders());
        self::assertSame([], $registry->getBootloaders());

        $registry = new BootloaderRegistry(
            ['BootloaderA', 'BootloaderB'],
            ['BootloaderC', 'BootloaderD'],
        );

        self::assertSame(['BootloaderA', 'BootloaderB'], $registry->getSystemBootloaders());
        self::assertSame(['BootloaderC', 'BootloaderD'], $registry->getBootloaders());
    }

    public function testSystemBootloaders(): void
    {
        $registry = new BootloaderRegistry();
        self::assertSame([], $registry->getSystemBootloaders());

        $registry->registerSystem('BootloaderA');
        $registry->registerSystem(['BootloaderB' => ['option' => 'value']]);

        self::assertSame([
            'BootloaderA',
            'BootloaderB' => ['option' => 'value'],
        ], $registry->getSystemBootloaders());
    }

    public function testBootloaders(): void
    {
        $registry = new BootloaderRegistry();
        self::assertSame([], $registry->getBootloaders());

        $registry->register('BootloaderA');
        $registry->register(['BootloaderB' => ['option' => 'value']]);

        self::assertSame([
            'BootloaderA',
            'BootloaderB' => ['option' => 'value'],
        ], $registry->getBootloaders());
    }

    public function testDuplicateBootloader(): void
    {
        $registry = new BootloaderRegistry();
        $registry->register('BootloaderA');
        $registry->register('BootloaderA');
        $registry->registerSystem('BootloaderA');

        self::assertSame(['BootloaderA'], $registry->getBootloaders());
        self::assertSame([], $registry->getSystemBootloaders());

        $registry = new BootloaderRegistry();
        $registry->registerSystem('BootloaderA');
        $registry->registerSystem('BootloaderA');
        $registry->register('BootloaderA');

        self::assertSame([], $registry->getBootloaders());
        self::assertSame(['BootloaderA'], $registry->getSystemBootloaders());
    }
}
