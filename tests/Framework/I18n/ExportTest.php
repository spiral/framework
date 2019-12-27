<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\I18n;

use Spiral\Framework\ConsoleTest;

class ExportTest extends ConsoleTest
{
    public function testReset(): void
    {
        $this->assertFileNotExists(sys_get_temp_dir() . '/messages.ru.php');

        $this->runCommandDebug('i18n:index');
        $this->runCommandDebug('configure');
        $this->runCommandDebug('i18n:export', [
            'locale'     => 'ru',
            'path'       => sys_get_temp_dir(),
            '--fallback' => 'en',
        ]);

        $this->assertFileExists(sys_get_temp_dir() . '/messages.ru.php');

        $exp = (require sys_get_temp_dir() . '/messages.ru.php');

        $this->assertArrayHasKey('World', $exp);
        $this->assertSame('Мир', $exp['World']);

        $this->assertArrayHasKey('%s unit|%s units', $exp);
        $this->assertArrayHasKey('This value is required.', $exp);

        unlink(sys_get_temp_dir() . '/messages.ru.php');
    }
}
