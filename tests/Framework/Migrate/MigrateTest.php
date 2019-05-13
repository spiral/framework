<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\Migrate;

use Spiral\Database\Database;
use Spiral\Framework\ConsoleTest;

class MigrateTest extends ConsoleTest
{
    public function setUp()
    {
        parent::setUp();

        $this->app->getEnvironment()->set('SAFE_MIGRATIONS', true);
    }

    public function testMigrate()
    {
        /** @var Database $db */
        $db = $this->app->get(Database::class);
        $this->assertSame([], $db->getTables());

        $out = $this->runCommandDebug('migrate');
        $this->assertContains('not', $out);

        $this->runCommandDebug('migrate:init');
        $this->runCommandDebug('cycle:migrate');

        $this->assertSame(1, count($db->getTables()));

        $this->runCommandDebug('migrate');

        $this->assertSame(3, count($db->getTables()));
    }

    public function testMigrateRollback()
    {
        /** @var Database $db */
        $db = $this->app->get(Database::class);
        $this->assertSame([], $db->getTables());

        $this->runCommandDebug('migrate:init');

        $out = $this->runCommandDebug('migrate:rollback');
        $this->assertContains('No', $out);

        $this->runCommandDebug('cycle:migrate');

        $this->assertSame(1, count($db->getTables()));

        $this->runCommandDebug('migrate');

        $this->assertSame(3, count($db->getTables()));

        $this->runCommandDebug('migrate:rollback');

        $this->assertSame(1, count($db->getTables()));
    }

    public function testMigrateReplay()
    {
        /** @var Database $db */
        $db = $this->app->get(Database::class);
        $this->assertSame([], $db->getTables());

        $this->runCommandDebug('migrate:init');

        $out = $this->runCommandDebug('migrate:replay');
        $this->assertContains('No', $out);

        $this->runCommandDebug('cycle:migrate');
        $this->assertSame(1, count($db->getTables()));

        $this->runCommandDebug('migrate');
        $this->assertSame(3, count($db->getTables()));

        $this->runCommandDebug('migrate:replay');
        $this->assertSame(3, count($db->getTables()));
    }
}