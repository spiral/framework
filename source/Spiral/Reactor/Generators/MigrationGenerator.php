<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators;

use Spiral\Database\Entities\Schemas\AbstractTable;
use Spiral\Database\Migrations\Migration;
use Spiral\Reactor\Generators\Prototypes\AbstractGenerator;

/**
 * Provides ability to generate migration file and automatically register it in migrator.
 */
class MigrationGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function generate()
    {
        $this->file->addUse(Migration::class);
        $this->file->addUse(AbstractTable::class);

        $this->class->setParent('Migration');
        $this->class->method('up', 'Executing migration.');
        $this->class->method('down', 'Dropping (rollback) migration.');
    }

    /**
     * Add code which is required to create and drop table.
     *
     * @param string $table
     * @return $this
     */
    public function createTable($table)
    {
        $table = var_export($table, true);
        $this->class->method('up')->setSource([
            "",
            "//Create table {$table}",
            "\$this->create({$table}, function(AbstractTable \$schema) {",
            "   \$schema->column('id')->primary();",
            "});"
        ]);

        $this->class->method('down')->setSource([
            "",
            "//Drop table {$table}",
            "\$this->schema({$table})->drop();"
        ], true);
    }

    /**
     * Add code which is required to alter table.
     *
     * @param string $table
     * @return $this
     */
    public function alterTable($table)
    {
        $table = var_export($table, true);

        $this->class->method('up')->setSource([
            "",
            "//Alter table {$table}",
            "\$this->alter({$table}, function(AbstractTable \$schema) {",
            "",
            "};"
        ]);

        $this->class->method('down')->setSource([
            "",
            "//Alter table {$table}",
            "\$this->alter({$table}, function(AbstractTable \$schema) {",
            "",
            "};"
        ]);
    }
}