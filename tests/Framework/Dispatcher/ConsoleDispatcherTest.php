<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Dispatcher;

use Spiral\Console\ConsoleDispatcher;
use Spiral\Tests\Framework\BaseTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class ConsoleDispatcherTest extends BaseTestCase
{
    public const MAKE_APP_ON_STARTUP = false;

    public function testCanServe(): void
    {
        $this->initApp();
        $this->assertDispatcherCanBeServed(ConsoleDispatcher::class);
    }

    public function testCanNotServe(): void
    {
        $this->initApp(['RR_MODE' => 'http']);
        $this->assertDispatcherCannotBeServed(ConsoleDispatcher::class);
    }

    public function testListCommands(): void
    {
        $output = new BufferedOutput();
        $this->initApp();

        $serveResult = $this->getContainer()->get(ConsoleDispatcher::class)
            ->serve(new ArrayInput([]), $output);
        $result = $output->fetch();

        $this->assertStringContainsString('dead', $result);
        $this->assertSame(0, $serveResult);
    }

    public function testException(): void
    {
        $output = new BufferedOutput();
        $this->initApp();

        $serveResult = $this->getContainer()->get(ConsoleDispatcher::class)->serve(
            new ArrayInput([
                'command' => 'dead',
            ]),
            $output
        );
        $result = $output->fetch();

        $this->assertStringContainsString('This command is dead', $result);
        $this->assertStringContainsString('DeadCommand.php', $result);
        $this->assertStringNotContainsString('throw new \InvalidArgumentException(\'This command is dead\');', $result);
        $this->assertNotEquals(0, $serveResult);
    }

    public function testExceptionVerbose(): void
    {
        $output = new BufferedOutput();
        $this->initApp();

        $output->setVerbosity(BufferedOutput::VERBOSITY_VERBOSE);
        $serveResult = $this->getContainer()->get(ConsoleDispatcher::class)->serve(
            new ArrayInput([
                'command' => 'dead',
            ]),
            $output
        );
        $result = $output->fetch();

        $this->assertStringContainsString('This command is dead', $result);
        $this->assertStringContainsString('DeadCommand.php', $result);
        $this->assertStringNotContainsString('throw new \InvalidArgumentException(\'This command is dead\');', $result);
        $this->assertNotEquals(0, $serveResult);
    }

    public function testExceptionDebug(): void
    {
        $output = new BufferedOutput();
        $this->initApp();

        $output->setVerbosity(BufferedOutput::VERBOSITY_DEBUG);

        $serveResult = $this->getContainer()->get(ConsoleDispatcher::class)->serve(
            new ArrayInput([
                'command' => 'dead',
            ]),
            $output
        );
        $result = $output->fetch();

        $this->assertStringContainsString('This command is dead', $result);
        $this->assertStringContainsString('DeadCommand.php', $result);
        $this->assertStringContainsString('throw new \InvalidArgumentException(\'This command is dead\');', $result);
        $this->assertNotEquals(0, $serveResult);
    }
}
