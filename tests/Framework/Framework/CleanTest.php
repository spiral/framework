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
        $output = $this->runCommand('cache:clean');
        $this->assertContains('Runtime cache has been cleared', $output);
    }

    public function testCleanError()
    {
        $this->runCommand('configure');

        $dir = $this->app->dir('cache');
        $f = fopen($dir . 'lock', 'w+');
        flock($f, LOCK_EX);

        $output = $this->runCommandDebug('cache:clean');
        $this->assertContains('[errored]', $output);

        fclose($f);
    }
}