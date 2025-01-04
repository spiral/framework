<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager\Checker;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager\Checker\BootloaderChecker;
use Spiral\Boot\BootloadManager\Checker\BootloaderCheckerInterface;
use Spiral\Boot\BootloadManager\Checker\CheckerRegistry;

final class BootloaderCheckerTest extends TestCase
{
    public function testCanInitializeFail(): void
    {
        $checker1 = $this->createMock(BootloaderCheckerInterface::class);
        $checker1->method('canInitialize')->willReturn(true);
        $checker2 = $this->createMock(BootloaderCheckerInterface::class);
        $checker2->method('canInitialize')->willReturn(false);

        $registry = new CheckerRegistry();
        $registry->register($checker1);
        $registry->register($checker2);

        $checker = new BootloaderChecker($registry);

        self::assertFalse($checker->canInitialize('foo'));
    }

    public function testCanInitializeSuccess(): void
    {
        $checker1 = $this->createMock(BootloaderCheckerInterface::class);
        $checker1->method('canInitialize')->willReturn(true);
        $checker2 = $this->createMock(BootloaderCheckerInterface::class);
        $checker2->method('canInitialize')->willReturn(true);

        $registry = new CheckerRegistry();
        $registry->register($checker1);
        $registry->register($checker2);

        $checker = new BootloaderChecker($registry);

        self::assertTrue($checker->canInitialize('foo'));
    }
}
