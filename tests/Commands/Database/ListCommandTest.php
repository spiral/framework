<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Database;

use Spiral\Tests\BaseTest;

class ListCommandTest extends BaseTest
{
    public function testList()
    {
        $tableB = $this->db->table('outer')->getSchema();
        $tableB->primary('id');
        $tableB->save();

        $table = $this->db->table('sample')->getSchema();
        $table->primary('primary_id');
        $table->string('some_string');
        $table->index(['some_string'])->setName('custom_index');
        $table->integer('b_id');
        $table->foreign('b_id')->references('outer', 'id');
        $table->save();


        $output = $this->console->run('db:list')->getOutput()->fetch();

        $this->assertContains('SQLite', $output);
        $this->assertContains('runtime', $output);
        $this->assertContains('other', $output);
        $this->assertContains('secondary_', $output);

        $this->assertContains('sample', $output);
        $this->assertContains('outer', $output);
    }
}