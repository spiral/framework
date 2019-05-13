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
}