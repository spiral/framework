<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\MemoryInterface;
use Spiral\Boot\NullMemory;

class NullMemoryTest extends TestCase
{
    public function testLoadData(): void
    {
        $memory = new NullMemory();
        self::assertInstanceOf(MemoryInterface::class, $memory);
        self::assertNull($memory->loadData('test'));
    }

    public function testSaveData(): void
    {
        $memory = new NullMemory();
        self::assertInstanceOf(MemoryInterface::class, $memory);
        $memory->saveData('test', null);
    }
}
