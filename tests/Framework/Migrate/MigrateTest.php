<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Migrate;

use Spiral\Database\Database;
use Spiral\Tests\Framework\ConsoleTest;

class MigrateTest extends ConsoleTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->getEnvironment()->set('SAFE_MIGRATIONS', true);
    }

    public function testMigrate(): void
    {
        /** @var Database $db */
        $db = $this->app->get(Database::class);
        $this->assertSame([], $db->getTables());

        $out = $this->runCommandDebug('migrate');
        $this->assertStringContainsString('not', $out);

        $this->runCommandDebug('migrate:init');
        $this->runCommandDebug('cycle:migrate');

        $this->assertSame(1, count($db->getTables()));

        $this->runCommandDebug('migrate');

        $this->assertSame(3, count($db->getTables()));
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

        $this->assertSame(1, count($db->getTables()));

        $this->runCommandDebug('migrate');

        $this->assertSame(3, count($db->getTables()));

        $this->runCommandDebug('migrate:rollback');

        $this->assertSame(1, count($db->getTables()));
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
        $this->assertSame(1, count($db->getTables()));

        $this->runCommandDebug('migrate');
        $this->assertSame(3, count($db->getTables()));

        $this->runCommandDebug('migrate:replay');
        $this->assertSame(3, count($db->getTables()));
    }
}
