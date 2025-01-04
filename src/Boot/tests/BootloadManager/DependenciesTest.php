<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager;

use Spiral\Tests\Boot\TestCase;
use Spiral\Tests\Boot\Fixtures\BootloaderA;
use Spiral\Tests\Boot\Fixtures\BootloaderB;

final class DependenciesTest extends TestCase
{
    public function testDep(): void
    {
        $b = $this->getBootloadManager();

        $b->bootload([BootloaderA::class]);

        self::assertTrue($this->container->has('a'));
        self::assertFalse($this->container->has('b'));
    }

    public function testDep2(): void
    {
        $b = $this->getBootloadManager();

        $b->bootload([BootloaderB::class]);

        self::assertTrue($this->container->has('a'));
        self::assertTrue($this->container->has('b'));
    }
}
