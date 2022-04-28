<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager\ClassesRegistry;
use Spiral\Tests\Boot\Fixtures\BootloaderA;
use Spiral\Tests\Boot\Fixtures\BootloaderB;

final class ClassesRegistryTest extends TestCase
{
    public function testEmptyByDefault(): void
    {
        $registry = new ClassesRegistry();

        $this->assertSame([], $registry->getClasses());
    }

    public function testRegister(): void
    {
        $registry = new ClassesRegistry();

        $registry->register(BootloaderA::class);
        $this->assertSame([BootloaderA::class], $registry->getClasses());

        $registry->register(BootloaderB::class);
        $this->assertSame([BootloaderA::class, BootloaderB::class], $registry->getClasses());
    }

    public function testIsBooted(): void
    {
        $registry = new ClassesRegistry();

        $this->assertFalse($registry->isBooted(BootloaderA::class));

        $registry->register(BootloaderA::class);
        $this->assertTrue($registry->isBooted(BootloaderA::class));
    }
}
