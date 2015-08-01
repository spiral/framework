<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ODM;

use Spiral\Console\Command;
use Spiral\ODM\SchemaBuilder;

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
    protected $name = 'odm:schema';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Update ODM schema.';

    /**
     * Update schema and documentation.
     */
    public function perform()
    {
        $this->debugger->benchmark($this, 'update');
        self::$schemaBuilder = $builder = $this->odm->updateSchema();
        $elapsed = number_format($this->debugger->benchmark($this, 'update'), 3);

        $countModels = count($builder->getDocumentSchemas());
        $this->write("<info>ODM Schema has been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", documents: <comment>{$countModels}</comment>.</info>");
    }
}