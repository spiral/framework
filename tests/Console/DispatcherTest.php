<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Console;

use Spiral\Tests\BaseTest;

class DispatcherTest extends BaseTest
{
    public function testConfigure()
    {
        $this->assertNotEmpty($this->app->console->getCommands());
    }

    /**
     * @expectedException \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    public function testRunUndefined()
    {
        $this->app->console->run('undefined');
    }
}