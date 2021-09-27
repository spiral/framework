<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Cycle;

use Spiral\Tests\Framework\ConsoleTest;

class MigrateTest extends ConsoleTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app = $this->makeApp([
            'SAFE_MIGRATIONS' => true
        ]);
    }

    public function testMigrate(): void
    {
        $this->runCommandDebug('migrate:init', ['-vvv' => true]);

        $output = $this->runCommandDebug('cycle:migrate');
        $this->assertStringContainsString('default.users', $output);

        $output = $this->runCommandDebug('cycle:migrate');
        $this->assertStringContainsString('Outstanding migrations found', $output);
    }

    public function testMigrateNoChanges(): void
    {
        $this->runCommandDebug('migrate:init', ['-vvv' => true]);

        $output = $this->runCommandDebug('cycle:migrate');
        $this->assertStringContainsString('default.users', $output);

        $this->runCommand('migrate');

        $output = $this->runCommandDebug('cycle:migrate');
        $this->assertStringContainsString('no database changes', $output);
    }

    public function testAlterSchema(): void
    {
        $this->runCommandDebug('migrate:init', ['-vvv' => true]);

        $output = $this->runCommandDebug('cycle:migrate', ['-r' => true]);
        $this->assertStringContainsString('default.users', $output);

        $user = file_get_contents(__DIR__ . '/../../app/src/User/User.php');
        unlink(__DIR__ . '/../../app/src/User/User.php');
        try {
            $output = $this->runCommandDebug('cycle:migrate', ['-r' => true]);
            $this->assertStringContainsString('drop foreign key', $output);
        } finally {
            file_put_contents(__DIR__ . '/../../app/src/User/User.php', $user);
        }
    }
}
