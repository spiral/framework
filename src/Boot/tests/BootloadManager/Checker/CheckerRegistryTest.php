<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager\Checker;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager\Checker\BootloaderCheckerInterface;
use Spiral\Boot\BootloadManager\Checker\CheckerRegistry;

final class CheckerRegistryTest extends TestCase
{
    public function testConstructor(): void
    {
        $registry = new CheckerRegistry();
        $this->assertSame([], $registry->getCheckers());
    }

    public function testRegistry(): void
    {
        $checker1 = $this->createMock(BootloaderCheckerInterface::class);
        $checker2 = $this->createMock(BootloaderCheckerInterface::class);

        $registry = new CheckerRegistry();
        $registry->register($checker1);
        $registry->register($checker2);

        $this->assertSame([$checker1, $checker2], $registry->getCheckers());
    }
}
