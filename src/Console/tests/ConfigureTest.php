<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Console\Command\ConfigureCommand;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Console;
use Spiral\Tests\Console\Fixtures\AnotherFailedCommand;
use Spiral\Tests\Console\Fixtures\FailedCommand;
use Spiral\Tests\Console\Fixtures\HelperCommand;
use Spiral\Tests\Console\Fixtures\TestCommand;
use Spiral\Tests\Console\Fixtures\UpdateClass;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ConfigureTest extends BaseTestCase
{
    public const TOKENIZER_CONFIG = [
        'directories' => [__DIR__.'/../src/Command', __DIR__.'/Fixtures/'],
        'exclude' => [],
    ];

    public const CONFIG = [
        'locateCommands' => false,
        'sequences' => [
            'configure' => [
                ['command' => 'test', 'header' => 'Test Command'],
                ['command' => 'helper', 'options' => ['helper' => 'writeln'], 'footer' => 'Good!'],
                ['invoke' => [UpdateClass::class, 'do']],
                ['invoke' => UpdateClass::class.'::do'],
                'Spiral\Tests\Console\ok',
                ['invoke' => UpdateClass::class.'::err'],
            ],
        ],
    ];

    /**
     * @throws Throwable
     */
    public function testConfigure(): void
    {
        $core = $this->getCore(
           $this->getStaticLocator([
               HelperCommand::class,
               ConfigureCommand::class,
               TestCommand::class,
           ])
        );

        $this->container->bind(Console::class, $core);

        $actual = $core->run('configure')->getOutput()->fetch();

        $expected = <<<'text'
            Configuring project:

            Test Command
            Hello World - 0
            hello
            Good!

            OK
            OK
            OK2
            exception

            All done!

            text;

        self::assertSame(\str_replace("\r", '', $expected), \str_replace("\r", '', $actual));
    }

    /**
     * @throws Throwable
     */
    public function testBreakFailure(): void
    {
        $core = $this->bindFailure();

        $output = $core->run('configure', ['--break' => true]);
        $result = $output->getOutput()->fetch();

        self::assertStringContainsString('Unhandled failed command error at', $result);
        self::assertStringContainsString('Aborting.', $result);
        self::assertStringNotContainsString('Unhandled another failed command error at', $result);
        self::assertSame(1, $output->getCode());
    }

    /**
     * @throws Throwable
     */
    public function testIgnoreAndBreakFailure(): void
    {
        $core = $this->bindFailure();

        $output = $core->run('configure', ['--ignore' => true, '--break' => true]);
        $result = $output->getOutput()->fetch();

        self::assertStringContainsString('Unhandled failed command error at', $result);
        self::assertStringNotContainsString('Aborting.', $result);
        self::assertStringContainsString('Unhandled another failed command error at', $result);
        self::assertSame(0, $output->getCode());
    }

    /**
     * @throws Throwable
     */
    public function testNoBreakFailure(): void
    {
        $core = $this->bindFailure();
        $this->container->bind(Console::class, $core);

        $output = $core->run('configure');
        $result = $output->getOutput()->fetch();

        self::assertStringContainsString('Unhandled failed command error at', $result);
        self::assertStringNotContainsString('Aborting.', $result);
        self::assertStringContainsString('Unhandled another failed command error at', $result);
        self::assertSame(1, $output->getCode());
    }

    private function bindFailure(): Console
    {
        $core = $this->getCore(
            $this->getStaticLocator([
                HelperCommand::class,
                ConfigureCommand::class,
                TestCommand::class,
                FailedCommand::class,
                AnotherFailedCommand::class,
            ])
        );
        $this->container->bind(
            ConsoleConfig::class,
            new ConsoleConfig([
                'locateCommands' => false,
                'sequences' => [
                    'configure' => [
                        ['command' => 'failed', 'header' => 'Failed Command'],
                        ['command' => 'failed:another', 'header' => 'Another failed Command'],
                    ],
                ]
            ])
        );
        $this->container->bind(Console::class, $core);

        return $core;
    }
}

function ok(OutputInterface $output): void
{
    $output->write('OK2');
}
