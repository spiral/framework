<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Command\Views;

use Spiral\Files\FilesInterface;
use Spiral\Tests\Framework\ConsoleTestCase;
use Symfony\Component\Console\Output\OutputInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(\Spiral\Command\Views\ResetCommand::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Spiral\Command\Views\CompileCommand::class)]
final class CompileCommandTest extends ConsoleTestCase
{
    public int $defaultVerbosityLevel = OutputInterface::VERBOSITY_DEBUG;

    public function testCompile(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('views:compile', strings: [
            'default:custom/file', 'custom:error', 'Unable to compile custom:error',
        ]);
    }

    public function testReset(): void
    {
        $this->getContainer()->get(FilesInterface::class)
            ->write($this->getDirectoryByAlias('cache', 'views/test.php'), 'test', null, true);

        $this->assertConsoleCommandOutputContainsStrings(
            'views:reset',
            strings: 'test.php',
        );
    }

    public function testResetClean(): void
    {
        $this->assertConsoleCommandOutputContainsStrings(
            'views:reset',
            strings: 'no cache',
        );
    }

    public function testClean(): void
    {
        $this->runCommand('i18n:index');

        $this->assertConsoleCommandOutputContainsStrings(
            'cache:clean',
            strings: 'i18n.en',
        );
    }
}
