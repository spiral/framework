<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Cycle;

use Spiral\Framework\ConsoleTest;

class MigrateTest extends ConsoleTest
{
    public function setUp()
    {
        $this->app = $this->makeApp([
            'SAFE_MIGRATIONS' => true
        ]);
    }

    public function testMigrate()
    {
        $this->runCommandDebug('migrate:init', ['-vvv' => true]);

        $output = $this->runCommandDebug('cycle:migrate');
        $this->assertContains('default.users', $output);

        $output = $this->runCommandDebug('cycle:migrate');
        $this->assertContains('Outstanding migrations found', $output);
    }

    public function testMigrateNoChanges()
    {
        $this->runCommandDebug('migrate:init', ['-vvv' => true]);

        $output = $this->runCommandDebug('cycle:migrate');
        $this->assertContains('default.users', $output);

        $this->runCommand('migrate');

        $output = $this->runCommandDebug('cycle:migrate');
        $this->assertContains('no database changes', $output);
    }

    public function testAlterSchema()
    {
        $this->runCommandDebug('migrate:init', ['-vvv' => true]);

        $output = $this->runCommandDebug('cycle:migrate', ['-r' => true]);
        $this->assertContains('default.users', $output);

        $user = file_get_contents(__DIR__ . '/../../app/src/User/User.php');
        unlink(__DIR__ . '/../../app/src/User/User.php');
        try {
            $output = $this->runCommandDebug('cycle:migrate', ['-r' => true]);
            $this->assertContains('drop foreign key', $output);
        } finally {
            file_put_contents(__DIR__ . '/../../app/src/User/User.php', $user);
        }
    }
}
