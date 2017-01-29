<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Database;

use Spiral\Tests\BaseTest;

class DescribeCommandTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Database\Exceptions\DBALException
     */
    public function testDescribeWrongDB()
    {
        $this->console->run('db:describe', [
            '--database' => 'missing',
            'table'      => 'missing'
        ]);
    }

    /**
     * @expectedException \Spiral\Database\Exceptions\DBALException
     */
    public function testDescribeWrongTable()
    {
        $this->console->run('db:describe', [
            '--database' => 'runtime',
            'table'      => 'missing'
        ]);
    }

    public function testDescribeExisted()
    {
        $table = $this->db->table('sample1')->getSchema();
        $table->primary('primary_id');
        $table->string('some_string');
        $table->index(['some_string'])->setName('custom_index');

        $table->save();

        $table = $this->db->table('sample')->getSchema();
        $table->primary('primary_id');

        $table->integer('primary1_id');
        $table->foreign('primary1_id')->references('samlple1', 'primary_id');

        $table->string('some_string');
        $table->index(['some_string'])->setName('custom_index');

        $table->save();

        $output = $this->console->run('db:describe', [
            '--database' => 'runtime',
            'table'      => 'sample'
        ]);

        $this->assertSame(0, $output->getCode());
        $output = $output->getOutput()->fetch();

        $this->assertContains('primary_id', $output);
        $this->assertContains('some_string', $output);
        $this->assertContains('custom_index', $output);
    }
}