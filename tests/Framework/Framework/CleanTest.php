<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Framework;

use Spiral\Framework\ConsoleTest;

/**
 * @covers \Spiral\Command\CleanCommand
 */
class CleanTest extends ConsoleTest
{
    public function testClean()
    {
        $this->runCommand('configure');

        $output = $this->runCommand('cache:clean');
        $this->assertContains('Runtime cache has been cleared', $output);
    }

    public function testClean2()
    {
        $output = $this->runCommand('cache:clean');
        $this->assertContains('Cache directory is missing', $output);
    }
}