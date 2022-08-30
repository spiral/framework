<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Database;

use Cycle\Database\Database;
use Cycle\Database\DatabaseManager;
use Spiral\Tests\Framework\ConsoleTest;

class ListTest extends ConsoleTest
{
    public function testList(): void
    {
        /** @var Database $db */
        $db = $this->app->get(Database::class);

        $tableB = $db->table('outer')->getSchema();
        $tableB->primary('id');
        $tableB->save();

        $table = $db->table('sample')->getSchema();
        $table->primary('primary_id');
        $table->string('some_string');
        $table->index(['some_string'])->setName('custom_index');
        $table->integer('b_id');
        $table->foreignKey(['b_id'])->references('outer', ['id']);
        $table->save();

        $output = $this->runCommand('db:list');
        $this->assertStringContainsString('SQLite', $output);
        $this->assertStringContainsString(':memory:', $output);
        $this->assertStringContainsString('sample', $output);
        $this->assertStringContainsString('outer', $output);
    }


    public function testBrokenList(): void
    {
        /** @var DatabaseManager $dm */
        $dm = $this->app->get(DatabaseManager::class);

        $dm->addDatabase(new Database(
            'other',
            '',
            $dm->driver('other')
        ));

        $output = $this->runCommand('db:list', ['db' => 'other']);
        $this->assertStringContainsString('Postgres', $output);
        $this->assertStringContainsString('database', $output);
        $this->assertStringContainsString('other', $output);
    }
}
