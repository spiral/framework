<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Bootloader;

use PHPUnit\Framework\TestCase;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Console\Config\ConsoleConfig;

final class ConsoleBootloaderTest extends TestCase
{
    private ConfigManager $configs;

    protected function setUp(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('has')->willReturn(false);

        $this->configs = new ConfigManager($loader);
        $this->configs->setDefaults(
            ConsoleConfig::CONFIG,
            [
                'commands' => [],
                'configure' => [],
                'update' => [],
            ]
        );
    }

    public function testSequencesNotDuplicated(): void
    {
        $bootloader = new ConsoleBootloader($this->configs);

        $bootloader->addUpdateSequence('cycle', 'test');
        $bootloader->addUpdateSequence('cycle', 'test2');
        $bootloader->addUpdateSequence('other', 'test3');

        $this->assertCount(2, $this->configs->getConfig(ConsoleConfig::CONFIG)['update']);
    }
}
