<?php

declare(strict_types=1);

namespace Framework\Bootloader\Debug;

use Spiral\Bootloader\DebugBootloader;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Debug\Config\DebugConfig;
use Spiral\Debug\StateInterface;
use Spiral\Testing\TestCase;

final class ScopedDebugBootloaderTest extends TestCase
{
    public function defineBootloaders(): array
    {
        return [
            DebugBootloader::class,
        ];
    }

    public function testAddCollectorFromOtherScope(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(DebugConfig::CONFIG, ['collectors' => []]);

        $bootloader = $this->getContainer()->get(DebugBootloader::class);
        $bootloader->addStateCollector('my-collector');
        $bootloader->addStateCollector('yet-another-collector');

        $state = $this->getContainer()->get(StateInterface::class);

        $this->assertInstanceOf(StateInterface::class, $state);
        $this->assertSame('my-collector, yet-another-collector', $state->getTags()['unresolved-collectors']);
    }
}
