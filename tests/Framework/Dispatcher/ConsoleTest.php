<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Dispatcher;

use Spiral\Console\ConsoleDispatcher;
use Spiral\Framework\BaseTest;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleTest extends BaseTest
{
    public function testCanServe()
    {
        $this->assertTrue($this->makeApp()->get(ConsoleDispatcher::class)->canServe());
    }

    public function testCanNotServe()
    {
        $this->assertFalse($this->makeApp([
            'RR' => true
        ])->get(ConsoleDispatcher::class)->canServe());
    }

    public function testListCommands()
    {
        $output = new BufferedOutput();
        $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([]), $output);

        $result = $output->fetch();

        $this->assertContains("dead", $result);
    }

    public function testException()
    {
        $output = new BufferedOutput();
        $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'dead'
        ]), $output);

        $result = $output->fetch();

        $this->assertContains("undefined", $result);
        $this->assertContains("DeadCommand.php", $result);
    }

    public function testExceptionVerbose()
    {
        $output = new BufferedOutput();
        $output->setVerbosity(BufferedOutput::VERBOSITY_VERBOSE);
        $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'dead'
        ]), $output);

        $result = $output->fetch();
        $this->assertContains("undefined", $result);
        $this->assertContains("DeadCommand.php", $result);
        $this->assertContains("->perform()", $result);
    }

    public function testExceptionDebug()
    {
        $output = new BufferedOutput();
        $output->setVerbosity(BufferedOutput::VERBOSITY_DEBUG);
        $this->makeApp()->get(ConsoleDispatcher::class)->serve(new ArrayInput([
            'command' => 'dead'
        ]), $output);

        $result = $output->fetch();
        $this->assertContains("undefined", $result);
        $this->assertContains("DeadCommand.php", $result);
        $this->assertContains("->perform()", $result);

        $this->assertContains("\$undefined", $result);
    }
}