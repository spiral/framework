<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Dispatcher;

use Spiral\Console\ConsoleDispatcher;
use Spiral\Framework\BaseTest;
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
        $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([]), $output);

        $result = $output->fetch();

        $this->assertContains('dead', $result);
    }

    public function testException(): void
    {
        $output = new BufferedOutput();
        $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'dead'
        ]), $output);

        $result = $output->fetch();

        $this->assertContains('undefined', $result);
        $this->assertContains('DeadCommand.php', $result);
    }

    public function testExceptionVerbose(): void
    {
        $output = new BufferedOutput();
        $output->setVerbosity(BufferedOutput::VERBOSITY_VERBOSE);
        $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'dead'
        ]), $output);

        $result = $output->fetch();
        $this->assertContains('undefined', $result);
        $this->assertContains('DeadCommand.php', $result);
    }

    public function testExceptionDebug(): void
    {
        $output = new BufferedOutput();
        $output->setVerbosity(BufferedOutput::VERBOSITY_DEBUG);
        $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'dead'
        ]), $output);

        $result = $output->fetch();
        $this->assertContains('undefined', $result);
        $this->assertContains('DeadCommand.php', $result);
        $this->assertContains('->perform()', $result);

        $this->assertContains('$undefined', $result);
    }
}
