<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Console;

use Spiral\Tests\BaseTest;

class VerbosityTest extends BaseTest
{
    public function testConfigureWithVerbosity()
    {
        $output = $this->console->run('configure', [
            '-vv' => true
        ])->getOutput()->fetch();

        $this->assertContains('All done!', $output);
    }
}