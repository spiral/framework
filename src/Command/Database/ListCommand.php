<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Database;

use Spiral\Console\Command;
use Spiral\Database\Config\DBALConfig;
use Spiral\Database\DBAL;
use Spiral\Database\Driver\AbstractDriver;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;

class ListCommand extends Command
{
    const NAME        = 'db:list';
    const DESCRIPTION = 'Get list of available databases, their tables and records count';
    const ARGUMENTS   = [
        ['db', InputArgument::OPTIONAL, 'Database name']
    ];

    /**
     * @param DBALConfig $config
     * @param DBAL       $dbal
     */
    public function perform(DBALConfig $config, DBAL $dbal)
    {
        if ($this->argument('db')) {
            $databases = [$this->argument('db')];
        } else {
            $databases = array_keys($config->getDatabases());
        }

        if (empty($databases)) {
            $this->writeln("<fg=red>No databases found.</fg=red>");
            return;
        }

        $grid = $this->table([
            'Name (ID):',
            'Database:',
            'Driver:',
            'Prefix:',
            'Status:',
            'Tables:',
            'Count Records:'
        ]);

        foreach ($databases as $database) {
            $database = $dbal->database($database);

            /** @var AbstractDriver $driver */
            $driver = $database->getDriver();

            $header = [
                $database->getName(),
                $driver->getSource(),
                $driver->getType(),
                $database->getPrefix() ?: '<comment>---</comment>'
            ];

            try {

                $driver->connect();
            } catch (\Exception $exception) {
                $grid->addRow(array_merge(
                    $header,
                    [
                        "<fg=red>{$exception->getMessage()}</fg=red>",
                        '<comment>---</comment>',
                        '<comment>---</comment>'
                    ]
                ));

                if ($database->getName() != end($databases)) {
                    $grid->addRow(new TableSeparator());
                }

                continue;
            }

            $header[] = "<info>connected</info>";
            foreach ($database->getTables() as $table) {
                $grid->addRow(array_merge(
                    $header,
                    [$table->getName(), number_format($table->count())]
                ));
                $header = ["", "", "", "", ""];
            }

            $header[1] && $grid->addRow(array_merge($header, ["no tables", "no records"]));
            if ($database->getName() != end($databases)) {
                $grid->addRow(new TableSeparator());
            }
        }

        $grid->render();
    }
}