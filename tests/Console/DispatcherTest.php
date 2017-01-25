<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Console;

use Spiral\Debug\QuickSnapshot;
use Spiral\Tests\BaseTest;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class DispatcherTest extends BaseTest
{
    public function testConfigure()
    {
        $this->assertNotEmpty($this->console->getCommands());
    }

    /**
     * @expectedException \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    public function testRunUndefined()
    {
        $this->console->run('undefined');
    }

    public function testStart()
    {
        $this->console->start(new ArrayInput([]), $output = new BufferedOutput());
        $this->assertContains('Spiral, Console Toolkit', $output->fetch());
    }

    public function testSnapshot()
    {
        $snapshot = new QuickSnapshot(new \Exception("Hello world"));

        $this->console->handleSnapshot($snapshot, $output = new BufferedOutput());
        $output = $output->fetch();
        $this->assertContains('Hello world', $output);
    }
}