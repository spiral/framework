<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager\Checker;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager\Checker\ClassExistsChecker;
use Spiral\Boot\Exception\ClassNotFoundException;
use Spiral\Tests\Boot\Fixtures\BootloaderA;

final class ClassExistsCheckerTest extends TestCase
{
    public function testCanInitialize(): void
    {
        $checker = new ClassExistsChecker();

        self::assertTrue($checker->canInitialize(BootloaderA::class));
        self::assertTrue($checker->canInitialize(new BootloaderA()));
    }

    public function testCanInitializeException(): void
    {
        $checker = new ClassExistsChecker();

        $this->expectException(ClassNotFoundException::class);
        $checker->canInitialize('foo');
    }
}
