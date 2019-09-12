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
    public function testReset()
    {
        $this->assertFileNotExists(__DIR__ . '/messages.ru.php');

        $this->runCommandDebug('i18n:index');
        $this->runCommandDebug('configure');
        $this->runCommandDebug('i18n:export', [
            'locale'     => 'ru',
            'path'       => __DIR__,
            '--fallback' => 'en',
        ]);

        $this->assertFileExists(__DIR__ . '/messages.ru.php');

        $exp = (require __DIR__ . '/messages.ru.php');

        $this->assertArrayHasKey('World', $exp);
        $this->assertSame('Мир', $exp['World']);

        $this->assertArrayHasKey('%s unit|%s units', $exp);
        $this->assertArrayHasKey('This value is required.', $exp);

        unlink(__DIR__ . '/messages.ru.php');
    }
}
