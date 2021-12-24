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
use Cycle\Database\Exception\DBALException;
use Spiral\Tests\Framework\ConsoleTest;

class DescribeTest extends ConsoleTest
{
    public function testDescribeWrongDB(): void
    {
        $this->expectException(DBALException::class);

        $this->runCommand('db:table', [
            '--database' => 'missing',
            'table'      => 'missing'
        ]);
    }

    public function testDescribeWrongTable(): void
    {
        $this->expectException(DBALException::class);

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

        $this->assertStringContainsString('primary_id', $output);
        $this->assertStringContainsString('some_int', $output);
        $this->assertStringContainsString('custom_index', $output);
        $this->assertStringContainsString('sample1', $output);
    }
}
