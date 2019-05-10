<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\Command\I18n;

use Spiral\Framework\ConsoleTest;

class ExportTest extends ConsoleTest
{
    public function testReset()
    {
        $this->assertFileNotExists(__DIR__ . '/messages.en.php');

        $this->runCommandDebug('i18n:index');
        $this->runCommandDebug('configure');
        $this->runCommandDebug('i18n:export', [
            'locale' => 'en',
            'path'   => __DIR__
        ]);

        $this->assertFileExists(__DIR__ . '/messages.en.php');

        $exp = (require __DIR__ . '/messages.en.php');

        $this->assertArrayHasKey('World', $exp);
        $this->assertArrayHasKey('%s unit|%s units', $exp);
        $this->assertArrayHasKey('This value is required.', $exp);

        unlink(__DIR__ . '/messages.en.php');
    }
}