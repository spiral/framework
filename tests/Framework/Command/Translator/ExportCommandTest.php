<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Command\Translator;

use Spiral\Tests\Framework\ConsoleTestCase;

final class ExportCommandTest extends ConsoleTestCase
{
    public function tearDown(): void
    {
        parent::tearDown();

        if (file_exists(sys_get_temp_dir().'/messages.ru.php')) {
            unlink(sys_get_temp_dir().'/messages.ru.php');
        }
    }

    public function testExport(): void
    {
        $this->assertFalse(is_file(sys_get_temp_dir().'/messages.ru.php'));

        $this->runCommand('i18n:index');

        $this->runCommand(
            'i18n:export',
            [
                'locale' => 'ru',
                'path' => sys_get_temp_dir(),
                '--fallback' => 'en',
            ]
        );

        $this->assertTrue(is_file(sys_get_temp_dir().'/messages.ru.php'));
    }
}
