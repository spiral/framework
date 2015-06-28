<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Reactor\Creators;

class MigrationCreator extends ClassCreator
{
    /**
     * ClassCreator used to render different declarations such as Controllers, Models and etc. All
     * rendering performed using Reactor classes.
     *
     * @param string $name Target class name.
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->file->setComment("This file is generated automatically on " . date('c') . ".");
        $this->file->addUse('Spiral\Components\DBAL\Migrations\Migration');

        $this->class->setParent('Migration');
        $this->class->method('up', 'Executing migration.');
        $this->class->method('down', 'Dropping (rollback) migration.');
    }

    /**
     * Add code which is required to create and drop table.
     *
     * @param string $table
     * @return static
     */
    public function createTable($table)
    {
        $table = var_export($table, true);

        $this->class->method('up')->setSource([
            "",
            "//Creating table \"" . func_get_arg(0) . "\"",
            "\$schema = \$this->schema($table);",
            "\$schema->column('id')->primary();",
            "",
            "\$schema->save();"
        ], true);

        $this->class->method('down')->setSource([
            "",
            "//Dropping table \"" . func_get_arg(0) . "\"",
            "\$this->schema($table)->drop();"
        ], true);
    }

    /**
     * Add code which is required to alter table.
     *
     * @param string $table
     * @return static
     */
    public function alterTable($table)
    {
        $table = var_export($table, true);

        $this->class->method('up')->setSource([
            "",
            "//Altering table \"" . func_get_arg(0) . "\"",
            "\$schema = \$this->schema($table);",
            "",
            "\$schema->save();"
        ], true);

        $this->class->method('down')->setSource([
            "",
            "//Rolling back changes in table \"" . func_get_arg(0) . "\"",
            "\$schema = \$this->schema($table);",
            "",
            "\$schema->save();"
        ], true);
    }
}