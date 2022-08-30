<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Command\Database;

use Spiral\Console\Command;
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Database;
use Cycle\Database\DatabaseManager;
use Cycle\Database\Driver\Driver;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @deprecated since v2.12. Will be removed in v3.0
 */
final class ListCommand extends Command
{
    protected const NAME        = 'db:list';
    protected const DESCRIPTION = 'Get list of available databases, their tables and records count';
    protected const ARGUMENTS   = [
        ['db', InputArgument::OPTIONAL, 'Database name'],
    ];

    /**
     * @param DatabaseConfig  $config
     * @param DatabaseManager $dbal
     */
    public function perform(DatabaseConfig $config, DatabaseManager $dbal): void
    {
        if ($this->argument('db')) {
            $databases = [$this->argument('db')];
        } else {
            $databases = array_keys($config->getDatabases());
        }

        if (empty($databases)) {
            $this->writeln('<fg=red>No databases found.</fg=red>');

            return;
        }

        $grid = $this->table(
            [
                'Name (ID):',
                'Database:',
                'Driver:',
                'Prefix:',
                'Status:',
                'Tables:',
                'Count Records:',
            ]
        );

        foreach ($databases as $database) {
            $database = $dbal->database($database);

            /** @var Driver $driver */
            $driver = $database->getDriver();

            $header = [
                $database->getName(),
                $driver->getSource(),
                $driver->getType(),
                $database->getPrefix() ?: '<comment>---</comment>',
            ];

            try {
                $driver->connect();
            } catch (\Exception $exception) {
                $this->renderException($grid, $header, $exception);

                if ($database->getName() != end($databases)) {
                    $grid->addRow(new TableSeparator());
                }

                continue;
            }

            $header[] = '<info>connected</info>';
            $this->renderTables($grid, $header, $database);
            if ($database->getName() != end($databases)) {
                $grid->addRow(new TableSeparator());
            }
        }

        $grid->render();
    }

    /**
     * @param Table      $grid
     * @param array      $header
     * @param \Throwable $exception
     */
    private function renderException(Table $grid, array $header, \Throwable $exception): void
    {
        $grid->addRow(
            array_merge(
                $header,
                [
                    "<fg=red>{$exception->getMessage()}</fg=red>",
                    '<comment>---</comment>',
                    '<comment>---</comment>',
                ]
            )
        );
    }

    /**
     * @param Table    $grid
     * @param array    $header
     * @param Database $database
     */
    private function renderTables(Table $grid, array $header, Database $database): void
    {
        foreach ($database->getTables() as $table) {
            $grid->addRow(
                array_merge(
                    $header,
                    [$table->getName(), number_format($table->count())]
                )
            );
            $header = ['', '', '', '', ''];
        }

        $header[1] && $grid->addRow(array_merge($header, ['no tables', 'no records']));
    }
}
