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

/**
 * Performs ORM schema update and binds schema builder in container.
 */
class SchemaCommand extends Command
{
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
        $benchmark = $this->debugger->benchmark($this, 'update');
        $builder = $this->orm->schemaBuilder();
        $this->container->bind(get_class($builder), $builder);

        if ($this->isVerbosing()) {
            foreach ($builder->getTables() as $table) {
                //So we can see what tables are doing
                $table->setLogger(
                    new ConsoleFormatter($this->output, $this->formats, $table->getName())
                );
            }
        }

        $this->orm->updateSchema($builder);
        $elapsed = number_format($this->debugger->benchmark($this, $benchmark), 3);

        $countModels = count($builder->getRecords());
        $this->write("<info>ORM Schema has been updated: <comment>{$elapsed} s</comment>");
        $this->writeln(", found records: <comment>{$countModels}</comment></info>");
    }
}