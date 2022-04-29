<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Framework;

use Spiral\Tests\Framework\ConsoleTest;

/**
 * @covers \Spiral\Command\CleanCommand
 */
class CleanTest extends ConsoleTest
{
    public function testClean(): void
    {
        $this->runCommand('configure');

        $output = $this->runCommand('cache:clean');
        $this->assertStringContainsString('Runtime cache has been cleared', $output);
    }

    public function testClean2(): void
    {
        $output = $this->runCommand('cache:clean');
        $this->assertStringContainsString('directory is missing', $output);
    }

    public function testCleanVerbose(): void
    {
        $this->runCommand('configure');

        $output = $this->runCommandDebug('cache:clean');
        $this->assertStringContainsString('i18n', $output);
    }
}
