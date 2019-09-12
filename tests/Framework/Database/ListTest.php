<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Database;

use Spiral\Database\Database;
use Spiral\Database\DatabaseManager;
use Spiral\Framework\ConsoleTest;

class ListTest extends ConsoleTest
{
    public function testList()
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
        $this->assertContains('SQLite', $output);
        $this->assertContains(':memory:', $output);
        $this->assertContains('sample', $output);
        $this->assertContains('outer', $output);
    }


    public function testBrokenList()
    {
        /** @var DatabaseManager $dm */
        $dm = $this->app->get(DatabaseManager::class);

        $dm->addDatabase(new Database(
            'other',
            '',
            $dm->driver('other')
        ));

        $output = $this->runCommand('db:list', ['db' => 'other']);
        $this->assertContains('Postgres', $output);
        $this->assertContains('database', $output);
        $this->assertContains('other', $output);
    }
}
