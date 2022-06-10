<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Views;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;
use Spiral\Tests\Framework\ConsoleTest;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \Spiral\Command\Views\ResetCommand
 * @covers \Spiral\Command\Views\CompileCommand
 */
final class CompileTest extends ConsoleTest
{
    public int $defaultVerbosityLevel = OutputInterface::VERBOSITY_DEBUG;

    public function testCompile(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('views:compile', strings: [
            'default:custom/file', 'custom:error', 'Unable to compile custom:error'
        ]);
    }

    public function testReset(): void
    {
        $this->getContainer()->get(FilesInterface::class)
            ->write($this->getDirectoryByAlias('cache', 'views/test.php'), 'test', null, true);

        $this->assertConsoleCommandOutputContainsStrings(
            'views:reset',
            strings: 'test.php'
        );
    }

    public function testResetClean(): void
    {
        $this->assertConsoleCommandOutputContainsStrings(
            'views:reset',
            strings: 'no cache'
        );
    }

    public function testClean(): void
    {
        $this->runCommand('i18n:index');

        $this->assertConsoleCommandOutputContainsStrings(
            'cache:clean',
            strings: 'i18n.en'
        );
    }
}
