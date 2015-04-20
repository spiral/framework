<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\DBAL;

use Spiral\Components\Console\Command;

use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;

class DatabasesCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'dbal:databases';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Get list of databases, their tables and records count.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = array(
        ['db', InputArgument::OPTIONAL, 'Database name.']
    );

    /**
     * Get list of databases, tables and connection status.
     */
    public function perform()
    {
        if ($this->argument('db'))
        {
            $databases = array($this->argument('db'));
        }
        else
        {
            $databases = array_keys($this->dbal->getConfig()['databases']);
        }

        if (empty($databases))
        {
            $this->writeln("No databases found.");

            return;
        }

        $grid = $this->table(array(
            'Name (ID):',
            'Database:',
            'Driver:',
            'Prefix:',
            'Status:',
            'Table Name:',
            'Count Records:'
        ));

        foreach ($databases as $database)
        {
            $database = $this->dbal->db($database);
            $driver = $database->getDriver();

            $header = array(
                $database->getName(),
                $database->getDriver()->databaseName(),
                $driver::DRIVER_NAME,
                $database->getPrefix() ?: "<comment>---</comment>"
            );

            try
            {
                $database->getDriver()->getPDO();
            }
            catch (\Exception $exception)
            {
                $grid->addRow(array_merge($header, array(
                    "<error>{$exception->getMessage()}</error>",
                    "<comment>---</comment>",
                    "<comment>---</comment>"
                )));

                if ($database->getName() != end($databases))
                {
                    $grid->addRow(new TableSeparator());
                }
                continue;
            }

            $header[] = "<info>connected</info>";
            foreach ($database->getTables() as $table)
            {
                $grid->addRow(array_merge(
                    $header,
                    array(
                        $table->getName(),
                        number_format($table->count())
                    )
                ));

                $header = array("", "", "", "", "");
            }

            $header[1] && $grid->addRow(array_merge($header, array("no tables", "no records")));
            if ($database->getName() != end($databases))
            {
                $grid->addRow(new TableSeparator());
            }
        }

        $grid->render();
    }
}