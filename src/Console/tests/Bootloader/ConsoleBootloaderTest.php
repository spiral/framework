<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Bootloader;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\AbstractKernel;
use Spiral\Command\CleanCommand;
use Spiral\Command\PublishCommand;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Core\FactoryInterface;

final class ConsoleBootloaderTest extends TestCase
{
    private ConfigManager $configs;

    protected function setUp(): void
    {
        $this->configs = $this->createConfigManager();
        $this->configs->setDefaults(
            ConsoleConfig::CONFIG,
            [
                'commands' => [],
                'configure' => [],
                'update' => [],
            ]
        );
    }

    public function testDefaultConfig(): void
    {
        $kernel = $this->createMock(AbstractKernel::class);
        $configs = $this->createConfigManager();

        (new ConsoleBootloader($configs))->boot($kernel, $this->createMock(FactoryInterface::class));

        $this->assertSame([
            'commands' => [
                CleanCommand::class,
                PublishCommand::class
            ],
            'configure' => [],
            'update' => [],
        ], $configs->getConfig(ConsoleConfig::CONFIG));
    }

    public function testAddCommand(): void
    {
        $bootloader = new ConsoleBootloader($this->configs);

        $bootloader->addCommand('test');
        $bootloader->addCommand('test2');
        $bootloader->addCommand('test3', true);

        $this->assertSame([
            'test3',
            'test',
            'test2'
        ], $this->configs->getConfig(ConsoleConfig::CONFIG)['commands']);
    }

    public function testUpdateSequence(): void
    {
        $bootloader = new ConsoleBootloader($this->configs);

        $bootloader->addUpdateSequence('cycle', 'test');
        $bootloader->addUpdateSequence('cycle', 'test2');
        $bootloader->addUpdateSequence('other', 'test3');

        $bootloader->addUpdateSequence(static fn () => 'test', 'test4');
        $bootloader->addUpdateSequence(static fn () => 'other', 'test5');

        $config = $this->configs->getConfig(ConsoleConfig::CONFIG)['update'];

        $this->assertCount(4, $config);
        $this->assertArrayHasKey('cycle', $config);
        $this->assertArrayHasKey('other', $config);
        $this->assertArrayHasKey(0, $config);
        $this->assertArrayHasKey(1, $config);
    }

    public function testConfigureSequence(): void
    {
        $bootloader = new ConsoleBootloader($this->configs);

        $bootloader->addConfigureSequence('cycle', 'test');
        $bootloader->addConfigureSequence('cycle', 'test2');
        $bootloader->addConfigureSequence('other', 'test3');

        $bootloader->addConfigureSequence(static fn () => 'test', 'test4');
        $bootloader->addConfigureSequence(static fn () => 'other', 'test5');

        $config = $this->configs->getConfig(ConsoleConfig::CONFIG)['configure'];

        $this->assertCount(4, $config);
        $this->assertArrayHasKey('cycle', $config);
        $this->assertArrayHasKey('other', $config);
        $this->assertArrayHasKey(0, $config);
        $this->assertArrayHasKey(1, $config);
    }

    private function createConfigManager(): ConfigManager
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('has')->willReturn(false);

        return new ConfigManager($loader);
    }
}
