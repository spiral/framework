<?php

declare(strict_types=1);

namespace Framework\Bootloader\Boot;

use Spiral\Boot\Memory;
use Spiral\Boot\MemoryInterface;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Logger\ListenerRegistry;
use Spiral\Logger\ListenerRegistryInterface;
use Spiral\Logger\LogFactory;
use Spiral\Logger\LogsInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class CoreBootloaderTest extends BaseTestCase
{
    public function testFilesInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(FilesInterface::class, Files::class);
    }

    public function testMemoryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(MemoryInterface::class, Memory::class);
    }

    public function testListenerRegistryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(ListenerRegistryInterface::class, ListenerRegistry::class);
    }

    public function testLogsInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(LogsInterface::class, LogFactory::class);
    }
}
