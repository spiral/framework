<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Database;

use Spiral\Tests\BaseTest;

class ListCommandTest extends BaseTest
{
    public function testList()
    {
        $output = $this->console->run('db:list')->getOutput()->fetch();

        $this->assertContains('SQLite', $output);
        $this->assertContains('runtime', $output);
        $this->assertContains('secondary', $output);
    }
}