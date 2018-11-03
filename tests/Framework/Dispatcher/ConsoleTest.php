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

        $this->assertContains("php:ext", $result);
        $this->assertContains("console:reload", $result);
    }
}