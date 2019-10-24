<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Views;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;
use Spiral\Framework\ConsoleTest;

/**
 * @covers \Spiral\Command\Views\ResetCommand
 * @covers \Spiral\Command\Views\CompileCommand
 */
class CompileTest extends ConsoleTest
{
    public function testCompile(): void
    {
        $out = $this->runCommandDebug('views:compile');
        $this->assertContains('default:custom/file', $out);

        $this->assertContains('custom:error', $out);
        $this->assertContains('Unable to compile custom:error', $out);
    }

    public function testReset(): void
    {
        /**
         * @var DirectoriesInterface $dirs
         * @var FilesInterface       $fs
         */
        $dirs = $this->app->get(DirectoriesInterface::class);
        $fs = $this->app->get(FilesInterface::class);
        $fs->write($dirs->get('cache') . '/views/test.php', 'test', null, true);

        $out = $this->runCommandDebug('views:reset');
        $this->assertContains('test.php', $out);
    }

    public function testResetClean(): void
    {
        $out = $this->runCommandDebug('views:reset');
        $this->assertContains('no cache', $out);
    }

    public function testClean(): void
    {
        $this->runCommandDebug('i18n:index');

        $out = $this->runCommandDebug('cache:clean');
        $this->assertContains('i18n.en', $out);
        $this->assertContains('i18n.locales', $out);
    }
}
