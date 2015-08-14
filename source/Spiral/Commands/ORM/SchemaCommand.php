<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ORM;

use Psr\Log\LogLevel;
use Spiral\Console\Command;
use Spiral\Console\Helpers\ConsoleFormatter;
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
     * Table schema log formats in verbosity mode.
     *
     * @var array
     */
    protected $formats = [
        LogLevel::INFO    => 'fg=cyan',
        LogLevel::DEBUG   => '',
        LogLevel::WARNING => 'fg=yellow'
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        $this->debugger->benchmark($this, 'update');
        $builder = $this->orm->schemaBuilder();

        if ($this->isVerbosing()) {
            foreach ($builder->getTables() as $table) {
                //So we can see what tables are doing
                $table->setLogger(
                    new ConsoleFormatter($this->output, $this->formats, $table->getName())
                );
            }
        }

        self::$schemaBuilder = $this->orm->updateSchema($builder);
        $elapsed = number_format($this->debugger->benchmark($this, 'update'), 3);

        $countModels = count($builder->getRecords());
        $this->write("<info>ORM Schema has been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", found records: <comment>{$countModels}</comment></info>");
    }
}