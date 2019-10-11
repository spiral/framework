<?php

declare(strict_types=1);

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Database;

use Spiral\Database\Database;
use Spiral\Framework\ConsoleTest;

class DescribeTest extends ConsoleTest
{
    /**
     * @expectedException \Spiral\Database\Exception\DBALException
     */
    public function testDescribeWrongDB(): void
    {
        $this->runCommand('db:table', [
            '--database' => 'missing',
            'table'      => 'missing'
        ]);
    }

    /**
     * @expectedException \Spiral\Database\Exception\DBALException
     */
    public function testDescribeWrongTable(): void
    {
        $this->runCommand('db:table', [
            '--database' => 'runtime',
            'table'      => 'missing'
        ]);
    }

    public function testDescribeExisted(): void
    {
        /** @var Database $db */
        $db = $this->app->get(Database::class);

        $table = $db->table('sample1')->getSchema();
        $table->primary('primary_id');
        $table->string('some_string');
        $table->index(['some_string'])->setName('custom_index_1');
        $table->save();

        $table = $db->table('sample')->getSchema();
        $table->primary('primary_id');
        $table->integer('primary1_id');
        $table->foreignKey(['primary1_id'])->references('sample1', ['primary_id']);
        $table->integer('some_int');
        $table->index(['some_int'])->setName('custom_index');
        $table->save();

        $output = $this->runCommand('db:table', [
            '--database' => 'default',
            'table'      => 'sample'
        ]);

        $this->assertContains('primary_id', $output);
        $this->assertContains('some_int', $output);
        $this->assertContains('custom_index', $output);
        $this->assertContains('sample1', $output);
    }
}
