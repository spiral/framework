<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\I18n;

use Spiral\Tests\Framework\ConsoleTest;

class ExportTest extends ConsoleTest
{
    public function tearDown(): void
    {
        parent::tearDown();

        if (file_exists(sys_get_temp_dir() . '/messages.ru.php')) {
            unlink(sys_get_temp_dir() . '/messages.ru.php');
        }
    }

    public function testReset(): void
    {
        $this->assertFileDoesNotExist(sys_get_temp_dir() . '/messages.ru.php');

        $this->runCommandDebug('i18n:index');
        $this->runCommandDebug('configure');

        $this->runCommandDebug(
            'i18n:export',
            [
                'locale'     => 'ru',
                'path'       => sys_get_temp_dir(),
                '--fallback' => 'en',
            ]
        );
    }
}
