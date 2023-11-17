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
        $this->assertSame([], $registry->getSystemBootloaders());
        $this->assertSame([], $registry->getBootloaders());

        $registry = new BootloaderRegistry(
            ['BootloaderA', 'BootloaderB'],
            ['BootloaderC', 'BootloaderD'],
        );

        $this->assertSame(['BootloaderA', 'BootloaderB'], $registry->getSystemBootloaders());
        $this->assertSame(['BootloaderC', 'BootloaderD'], $registry->getBootloaders());
    }

    public function testSystemBootloaders(): void
    {
        $registry = new BootloaderRegistry();
        $this->assertSame([], $registry->getSystemBootloaders());

        $registry->registerSystem('BootloaderA');
        $registry->registerSystem(['BootloaderB' => ['option' => 'value']]);

        $this->assertSame([
            'BootloaderA',
            'BootloaderB' => ['option' => 'value'],
        ], $registry->getSystemBootloaders());
    }

    public function testBootloaders(): void
    {
        $registry = new BootloaderRegistry();
        $this->assertSame([], $registry->getBootloaders());

        $registry->register('BootloaderA');
        $registry->register(['BootloaderB' => ['option' => 'value']]);

        $this->assertSame([
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

        $this->assertSame(['BootloaderA'], $registry->getBootloaders());
        $this->assertSame([], $registry->getSystemBootloaders());

        $registry = new BootloaderRegistry();
        $registry->registerSystem('BootloaderA');
        $registry->registerSystem('BootloaderA');
        $registry->register('BootloaderA');

        $this->assertSame([], $registry->getBootloaders());
        $this->assertSame(['BootloaderA'], $registry->getSystemBootloaders());
    }
}
