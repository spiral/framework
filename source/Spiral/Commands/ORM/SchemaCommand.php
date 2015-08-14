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
use Spiral\ORM\Entities\SchemaBuilder;

/**
 * Performs ORM schema update and stores SchemaBuilder in public static variable for other commands.
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
    protected $name = 'orm:schema';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Update ORM schema.';

    /**
     * Perform command.
     */
    public function perform()
    {
        $this->debugger->benchmark($this, 'update');
        self::$schemaBuilder = $builder = $this->orm->updateSchema();
        $elapsed = number_format($this->debugger->benchmark($this, 'update'), 3);

        $countModels = count($builder->getRecords());
        $this->write("<info>ORM Schema has been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", found records: <comment>{$countModels}</comment></info>");
    }
}