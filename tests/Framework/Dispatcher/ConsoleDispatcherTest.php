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

        self::assertStringContainsString('dead', $result);
        self::assertSame(0, $serveResult);
    }

    public function testException(): void
    {
        $output = new BufferedOutput();
        $this->initApp();

        $serveResult = $this->getContainer()->get(ConsoleDispatcher::class)->serve(
            new ArrayInput([
                'command' => 'dead',
            ]),
            $output,
        );
        $result = $output->fetch();

        self::assertStringContainsString('This command is dead', $result);
        self::assertStringContainsString('DeadCommand.php', $result);
        self::assertStringNotContainsString('throw new \InvalidArgumentException(\'This command is dead\');', $result);
        self::assertNotEquals(0, $serveResult);
    }

    public function testExceptionVerbose(): void
    {
        $this->initApp();

        $output = new BufferedOutput();
        $serveResult = $this->getContainer()->get(ConsoleDispatcher::class)->serve(
            new ArrayInput([
                'command' => 'dead',
                '-vv',
            ]),
            $output,
        );
        $result = $output->fetch();

        self::assertStringContainsString('This command is dead', $result);
        self::assertStringContainsString('DeadCommand.php', $result);
        self::assertStringNotContainsString('throw new \InvalidArgumentException(\'This command is dead\');', $result);
        self::assertNotEquals(0, $serveResult);
    }

    public function testExceptionDebug(): void
    {
        $this->initApp();

        $output = new BufferedOutput();
        $serveResult = $this->getContainer()->get(ConsoleDispatcher::class)->serve(
            new ArrayInput([
                'command' => 'dead',
                '-vvv',
            ]),
            $output,
        );
        $result = $output->fetch();

        self::assertStringContainsString('This command is dead', $result);
        self::assertStringContainsString('DeadCommand.php', $result);
        self::assertStringContainsString('throw new \InvalidArgumentException(\'This command is dead\');', $result);
        self::assertNotEquals(0, $serveResult);
    }
}
