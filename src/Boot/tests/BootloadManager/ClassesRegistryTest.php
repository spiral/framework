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

        self::assertSame([], $registry->getClasses());
    }

    public function testRegister(): void
    {
        $registry = new ClassesRegistry();

        $registry->register(BootloaderA::class);
        self::assertSame([BootloaderA::class], $registry->getClasses());

        $registry->register(BootloaderB::class);
        self::assertSame([BootloaderA::class, BootloaderB::class], $registry->getClasses());
    }

    public function testIsBooted(): void
    {
        $registry = new ClassesRegistry();

        self::assertFalse($registry->isBooted(BootloaderA::class));

        $registry->register(BootloaderA::class);
        self::assertTrue($registry->isBooted(BootloaderA::class));
    }
}
