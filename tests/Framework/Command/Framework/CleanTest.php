<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Command\Framework;

use Spiral\Framework\Command\BaseCommandTest;

class CleanTest extends BaseCommandTest
{
    public function testClean()
    {
        $output = $this->runCommand('clean:cache');
        $this->assertContains('Runtime cache has been cleared', $output);
    }
}