<?php

declare(strict_types=1);

namespace Framework\Bootloader\Console;

use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Console\CommandLocator;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Console;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Console\LocatorInterface;
use Spiral\Console\Sequence\CommandSequence;
use Spiral\Tests\Framework\BaseTestCase;
use Symfony\Component\Console\Output\BufferedOutput;

final class ConsoleBootloaderTest extends BaseTestCase
{
    public function testConsoleBinding(): void
    {
        $this->assertContainerBoundAsSingleton(Console::class, Console::class);
    }

    //    public function testLocatorInterfaceBinding(): void
    //    {
    //        $this->assertContainerBoundAsSingleton(LocatorInterface::class, CommandLocator::class);
    //    }

    public function testConsoleDispatcher(): void
    {
        $this->assertDispatcherRegistered(ConsoleDispatcher::class);
    }

    public function testAddInterceptor(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(ConsoleConfig::CONFIG, ['interceptors' => []]);

        $bootloader = new ConsoleBootloader($configs);
        $bootloader->addInterceptor('foo');
        $bootloader->addInterceptor('bar');

        self::assertSame([
            'foo', 'bar',
        ], $configs->getConfig(ConsoleConfig::CONFIG)['interceptors']);
    }

    public function testAddCommand(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(ConsoleConfig::CONFIG, ['commands' => []]);

        $bootloader = new ConsoleBootloader($configs);
        $bootloader->addCommand('foo');
        $bootloader->addCommand('bar');
        $bootloader->addCommand('baz', true);
        $bootloader->addCommand('baf');

        self::assertSame([
            'baz', 'foo', 'bar', 'baf',
        ], $configs->getConfig(ConsoleConfig::CONFIG)['commands']);
    }

    public function testAddConfigureSequence(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(ConsoleConfig::CONFIG, ['sequences' => []]);

        $bootloader = new ConsoleBootloader($configs);
        $bootloader->addConfigureSequence('foo', 'header', 'footer');

        $sequences = $configs->getConfig(ConsoleConfig::CONFIG)['sequences']['configure'];

        $output = new BufferedOutput();
        self::assertInstanceOf(CommandSequence::class, $sequences['foo']);
        $sequences['foo']->writeHeader($output);
        $sequences['foo']->writeFooter($output);
        self::assertSame("header\nfooter\n", \str_replace(PHP_EOL, "\n", $output->fetch()));
    }

    public function testAddUpdateSequence(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(ConsoleConfig::CONFIG, ['sequences' => []]);

        $bootloader = new ConsoleBootloader($configs);
        $bootloader->addUpdateSequence('foo', 'header', 'footer');

        $sequences = $configs->getConfig(ConsoleConfig::CONFIG)['sequences']['update'];

        $output = new BufferedOutput();
        self::assertInstanceOf(CommandSequence::class, $sequences['foo']);
        $sequences['foo']->writeHeader($output);
        $sequences['foo']->writeFooter($output);
        self::assertSame("header\nfooter\n", \str_replace(PHP_EOL, "\n", $output->fetch()));
    }

    public function testAddCustomSequence(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(ConsoleConfig::CONFIG, ['sequences' => []]);

        $bootloader = new ConsoleBootloader($configs);
        $bootloader->addSequence('custom', 'foo', 'header', 'footer');

        $sequences = $configs->getConfig(ConsoleConfig::CONFIG)['sequences']['custom'];

        $output = new BufferedOutput();
        self::assertInstanceOf(CommandSequence::class, $sequences['foo']);
        $sequences['foo']->writeHeader($output);
        $sequences['foo']->writeFooter($output);
        self::assertSame("header\nfooter\n", \str_replace(PHP_EOL, "\n", $output->fetch()));
    }

    public function testSequencesIsNotDuplicated(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(ConsoleConfig::CONFIG, ['sequences' => []]);

        $bootloader = new ConsoleBootloader($configs);

        $bootloader->addUpdateSequence('cycle', 'test');
        $bootloader->addUpdateSequence('cycle', 'test2');
        $bootloader->addUpdateSequence('other', 'test3');

        $bootloader->addUpdateSequence(static fn(): string => 'test', 'test4');
        $bootloader->addUpdateSequence(static fn(): string => 'other', 'test5');

        $config = $configs->getConfig(ConsoleConfig::CONFIG)['sequences']['update'];

        self::assertCount(4, $config);
        self::assertArrayHasKey('cycle', $config);
        self::assertArrayHasKey('other', $config);
        self::assertArrayHasKey(0, $config);
        self::assertArrayHasKey(1, $config);
    }
}
