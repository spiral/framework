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
        /** @var Database $db */
        $db = $this->app->get(Database::class);
        $this->assertSame([], $db->getTables());

        $this->runCommandDebug('migrate:init');
        $this->runCommandDebug('cycle:migrate');

        $this->assertCount(0, $db->getTables());

        $this->runCommandDebug('migrate');
        $this->assertCount(3, $db->getTables());
    }

    public function testMigrateRollback(): void
    {
        /** @var Database $db */
        $db = $this->app->get(Database::class);
        $this->assertSame([], $db->getTables());

        $this->runCommandDebug('migrate:init');

        $out = $this->runCommandDebug('migrate:rollback');
        $this->assertStringContainsString('No', $out);

        $this->runCommandDebug('cycle:migrate');

        $this->assertCount(0, $db->getTables());

        $this->runCommandDebug('migrate');

        $this->assertCount(3, $db->getTables());

        $this->runCommandDebug('migrate:rollback');

        $this->assertCount(1, $db->getTables());
    }

    public function testMigrateReplay(): void
    {
        /** @var Database $db */
        $db = $this->app->get(Database::class);
        $this->assertSame([], $db->getTables());

        $this->runCommandDebug('migrate:init');

        $out = $this->runCommandDebug('migrate:replay');
        $this->assertStringContainsString('No', $out);

        $this->runCommandDebug('cycle:migrate');
        $this->assertCount(0, $db->getTables());

        $this->runCommandDebug('migrate');
        $this->assertCount(3, $db->getTables());

        $this->runCommandDebug('migrate:replay');
        $this->assertCount(3, $db->getTables());
    }
}
