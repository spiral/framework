<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ORM;

use Spiral\Console\Command;
use Spiral\ORM\SchemaBuilder;

class SchemaCommand extends Command
{
    /**
     * Schema builder instance.
     *
     * @var SchemaBuilder
     */
    public static $schemaBuilder = null;

    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'orm:schema';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Update ORM schema and related databases.';

    /**
     * Update schema and documentation.
     */
    public function perform()
    {
        $this->debugger->benchmark($this, 'update');
        self::$schemaBuilder = $builder = $this->orm->updateSchema();
        $elapsed = number_format($this->debugger->benchmark($this, 'update'), 3);

        $countModels = count($builder->getModelSchemas());
        $this->write("<info>ODM Schema has been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", models: <comment>{$countModels}</comment>.</info>");
    }
}