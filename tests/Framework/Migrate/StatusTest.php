<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Migrate;

use Cycle\Database\Database;
use Spiral\Tests\Framework\ConsoleTest;

class StatusTest extends ConsoleTest
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
        /** @var Database $db */
        $db = $this->app->get(Database::class);
        $this->assertSame([], $db->getTables());

        $out = $this->runCommandDebug('migrate:status');
        $this->assertStringContainsString('No migrations', $out);

        $this->runCommandDebug('migrate:init');

        $out = $this->runCommandDebug('migrate:status');
        $this->assertStringContainsString('No migrations', $out);

        $this->runCommandDebug('cycle:migrate');
        $this->assertCount(0, $db->getTables());

        $out = $this->runCommandDebug('migrate:status');
        $this->assertStringContainsString('not executed yet', $out);

        $this->runCommandDebug('migrate');
        $this->assertCount(3, $db->getTables());

        $out2 = $this->runCommandDebug('migrate:status');
        $this->assertNotSame($out, $out2);
    }
}
