<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Framework;

use Spiral\Framework\ConsoleTest;

/**
 * @covers \Spiral\Command\CleanCommand
 */
class CleanTest extends ConsoleTest
{
    public function testClean(): void
    {
        $this->runCommand('configure');

        $output = $this->runCommand('cache:clean');
        $this->assertContains('Runtime cache has been cleared', $output);
    }

    public function testClean2(): void
    {
        $output = $this->runCommand('cache:clean');
        $this->assertContains('directory is missing', $output);
    }

    public function testCleanVerbose(): void
    {
        $this->runCommand('configure');

        $output = $this->runCommandDebug('cache:clean');
        $this->assertContains('i18n', $output);
    }

    public function testUpdateClean(): void
    {
        $out = $this->runCommand('update');
        $this->assertContains('Updating ORM schema', $out);

        $output = $this->runCommandDebug('cache:clean');
        $this->assertContains('cycle.php', $output);
    }
}
