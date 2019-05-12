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

class CompileTest extends ConsoleTest
{
    public function testCompile()
    {
        $out = $this->runCommandDebug('views:compile');
        $this->assertContains('default:native', $out);

        $this->assertContains('custom:error', $out);
        $this->assertContains('Unable to compile custom:error', $out);
    }

    public function testReset()
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

    public function testResetClean()
    {
        $out = $this->runCommandDebug('views:reset');
        $this->assertContains('no cache', $out);
    }

    public function testClean()
    {
        $this->runCommandDebug('i18n:index');

        $out = $this->runCommandDebug('cache:clean');
        $this->assertContains('i18n.en', $out);
        $this->assertContains('i18n.locales', $out);
    }
}