<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Console\Command\UpdateCommand;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Console;
use Spiral\Tests\Console\Fixtures\AnotherFailedCommand;
use Spiral\Tests\Console\Fixtures\FailedCommand;
use Spiral\Tests\Console\Fixtures\HelperCommand;
use Spiral\Tests\Console\Fixtures\TestCommand;
use Spiral\Tests\Console\Fixtures\UpdateClass;
use Throwable;

class UpdateTest extends BaseTestCase
{
    public const TOKENIZER_CONFIG = [
        'directories' => [__DIR__.'/../src/Command', __DIR__.'/Fixtures/'],
        'exclude' => [],
    ];

    public const CONFIG = [
        'locateCommands' => false,
        'commands' => [],
        'sequences' => [
            'update' => [
                ['command' => 'test', 'header' => 'Test Command'],
                ['command' => 'helper', 'options' => ['helper' => 'writeln'], 'footer' => 'Good!'],
                ['invoke' => [UpdateClass::class, 'do']],
                ['invoke' => UpdateClass::class.'::do'],
                'Spiral\Tests\Console\ok',
                ['invoke' => UpdateClass::class.'::err'],
            ],
        ],
    ];

    public function testConfigure(): void
    {
        $core = $this->getCore(
            $this->getStaticLocator([
                HelperCommand::class,
                TestCommand::class,
                UpdateCommand::class,
            ])
        );

        $this->container->bind(Console::class, $core);

        $actual = $core->run('update')->getOutput()->fetch();

        $expected = <<<'text'
Updating project state:

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

        $this->assertSame(
            \str_replace("\r", '', $expected),
            \str_replace("\r", '', $actual)
        );
    }

    /**
     * @throws Throwable
     */
    public function testBreakFailure(): void
    {
        $core = $this->bindFailure();

        $output = $core->run('update', ['--break' => true]);
        $result = $output->getOutput()->fetch();

        $this->assertStringContainsString('Unhandled failed command error at', $result);
        $this->assertStringContainsString('Aborting.', $result);
        $this->assertStringNotContainsString('Unhandled another failed command error at', $result);
        $this->assertEquals(1, $output->getCode());
    }

    /**
     * @throws Throwable
     */
    public function testIgnoreAndBreakFailure(): void
    {
        $core = $this->bindFailure();

        $output = $core->run('update', ['--ignore' => true, '--break' => true]);
        $result = $output->getOutput()->fetch();

        $this->assertStringContainsString('Unhandled failed command error at', $result);
        $this->assertStringNotContainsString('Aborting.', $result);
        $this->assertStringContainsString('Unhandled another failed command error at', $result);
        $this->assertEquals(0, $output->getCode());
    }

    /**
     * @throws Throwable
     */
    public function testNoBreakFailure(): void
    {
        $core = $this->bindFailure();
        $this->container->bind(Console::class, $core);

        $output = $core->run('update');
        $result = $output->getOutput()->fetch();

        $this->assertStringContainsString('Unhandled failed command error at', $result);
        $this->assertStringNotContainsString('Aborting.', $result);
        $this->assertStringContainsString('Unhandled another failed command error at', $result);
        $this->assertEquals(1, $output->getCode());
    }

    private function bindFailure(): Console
    {
        $core = $this->getCore(
            $this->getStaticLocator([
                HelperCommand::class,
                TestCommand::class,
                UpdateCommand::class,
                FailedCommand::class,
                AnotherFailedCommand::class,
            ])
        );
        $this->container->bind(
            ConsoleConfig::class,
            new ConsoleConfig([
                'locateCommands' => false,
                'commands' => [],
                'sequences' => [
                    'update' => [
                        ['command' => 'failed', 'header' => 'Failed Command'],
                        ['command' => 'failed:another', 'header' => 'Another failed Command'],
                    ],
                ],
            ])
        );
        $this->container->bind(Console::class, $core);

        return $core;
    }
}
