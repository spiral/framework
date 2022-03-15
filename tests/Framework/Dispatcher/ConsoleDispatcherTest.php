<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Dispatcher;

use Spiral\Console\ConsoleDispatcher;
use Spiral\Tests\Framework\BaseTest;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleDispatcherTest extends BaseTest
{
    public function testCanServe(): void
    {
        $this->assertTrue($this->makeApp()->get(ConsoleDispatcher::class)->canServe());
    }

    public function testCanNotServe(): void
    {
        $this->assertFalse($this->makeApp([
            'RR' => true
        ])->get(ConsoleDispatcher::class)->canServe());
    }

    public function testListCommands(): void
    {
        $output = new BufferedOutput();
        $serveResult = $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([]), $output);
        $result = $output->fetch();

        $this->assertStringContainsString('dead', $result);
        $this->assertSame(0, $serveResult);
    }

    public function testException(): void
    {
        $output = new BufferedOutput();
        $serveResult = $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'dead'
        ]), $output);
        $result = $output->fetch();

        $this->assertStringContainsString('undefined', $result);
        $this->assertStringContainsString('DeadCommand.php', $result);
        $this->assertNotEquals(0, $serveResult);
    }

    public function testExceptionVerbose(): void
    {
        $output = new BufferedOutput();
        $output->setVerbosity(BufferedOutput::VERBOSITY_VERBOSE);
        $serveResult = $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'dead'
        ]), $output);
        $result = $output->fetch();

        $this->assertStringContainsString('undefined', $result);
        $this->assertStringContainsString('DeadCommand.php', $result);
        $this->assertNotEquals(0, $serveResult);
    }

    public function testExceptionDebug(): void
    {
        $output = new BufferedOutput();
        $output->setVerbosity(BufferedOutput::VERBOSITY_DEBUG);
        $serveResult = $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'dead'
        ]), $output);
        $result = $output->fetch();

        $this->assertStringContainsString('undefined', $result);
        $this->assertStringContainsString('DeadCommand.php', $result);
        $this->assertStringContainsString('$undefined', $result);
        $this->assertNotEquals(0, $serveResult);
    }
}
