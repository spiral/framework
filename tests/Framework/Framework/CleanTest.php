<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Framework;

use Spiral\Framework\ConsoleTest;

class CleanTest extends ConsoleTest
{
    public function testClean()
    {
        $output = $this->runCommand('clean:cache');
        $this->assertContains('Runtime cache has been cleared', $output);
    }
}