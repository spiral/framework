<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Command;

use Spiral\Console\Sequence\RuntimeDirectory;
use Spiral\Tests\Framework\ConsoleTestCase;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \Spiral\Command\CleanCommand
 */
final class CleanCommandTest extends ConsoleTestCase
{
    public int $defaultVerbosityLevel = OutputInterface::VERBOSITY_DEBUG;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getContainer()->get(RuntimeDirectory::class)->ensure(new NullOutput());
    }

    public function testClean(): void
    {
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
        $this->runCommand('i18n:index');

        $this->assertConsoleCommandOutputContainsStrings('cache:clean', strings: [
            'i18n'
        ]);
    }
}
