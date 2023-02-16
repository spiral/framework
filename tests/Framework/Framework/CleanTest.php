<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Framework;

use Spiral\Tests\Framework\ConsoleTest;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \Spiral\Command\CleanCommand
 */
final class CleanTest extends ConsoleTest
{
    public int $defaultVerbosityLevel = OutputInterface::VERBOSITY_DEBUG;

    public function testClean(): void
    {
        $this->runCommand('configure');

        $this->assertConsoleCommandOutputContainsStrings('cache:clean', strings: [
            'Runtime cache has been cleared'
        ]);
    }

    public function testCleanWhenRuntimeDirectoryNotExists(): void
    {
        $this->cleanUpRuntimeDirectory();
        $this->assertConsoleCommandOutputContainsStrings('cache:clean', strings: [
            'directory is missing'
        ]);
    }

    public function testCleanVerbose(): void
    {
        $this->runCommand('configure');

        $this->assertConsoleCommandOutputContainsStrings('cache:clean', strings: [
            'i18n'
        ]);
    }
}
