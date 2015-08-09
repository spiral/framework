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
use Spiral\ODM\Entities\SchemaBuilder;

/**
 * Performs ODM schema update and stores SchemaBuilder in public static variable for other commands.
 */
class SchemaCommand extends Command
{
    /**
     * @var SchemaBuilder
     */
    public static $schemaBuilder = null;

    /**
     * {@inheritdoc}
     */
    protected $name = 'odm:schema';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Update ODM schema.';

    /**
     * Perform command.
     */
    public function perform()
    {
        $this->debugger->benchmark($this, 'update');
        self::$schemaBuilder = $builder = $this->odm->updateSchema();
        $elapsed = number_format($this->debugger->benchmark($this, 'update'), 3);

        $countModels = count($builder->getDocuments());
        $this->write("<info>ODM Schema has been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", found documents: <comment>{$countModels}</comment></info>");
    }
}