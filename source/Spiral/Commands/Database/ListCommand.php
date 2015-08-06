<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Database;

use Spiral\Console\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;

/**
 * List of every configured database, it's tables and count of records.
 */
class ListCommand extends Command
{
    /**
     * No information available placeholder.
     */
    const SKIP = '<comment>---</comment>';

    /**
     * {@inheritdoc}
     */
    protected $name = 'db:list';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Get list of available databases, their tables and records count.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['db', InputArgument::OPTIONAL, 'Database name.']
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        if ($this->argument('db')) {
            $databases = [$this->argument('db')];
        } else {
            //Every available database
            $databases = array_keys($this->dbal->config()['databases']);
        }

        if (empty($databases)) {
            $this->writeln("<fg=red>No databases found.</fg=red>");

            return;
        }

        $grid = $this->tableHelper([
            'Name (ID):',
            'Database:',
            'Driver:',
            'Prefix:',
            'Status:',
            'Tables:',
            'Count Records:'
        ]);

        foreach ($databases as $database) {
            $database = $this->dbal->db($database);
            $driver = $database->driver();

            $header = [
                $database->getName(),
                $driver->getSource(),
                $driver->getType(),
                $database->getPrefix() ?: self::SKIP
            ];

            try {
                $driver->connect();
            } catch (\Exception $exception) {
                $grid->addRow(array_merge($header, [
                    "<fg=red>{$exception->getMessage()}</fg=red>",
                    self::SKIP,
                    self::SKIP
                ]));

                if ($database->getName() != end($databases)) {
                    $grid->addRow(new TableSeparator());
                }

                continue;
            }

            $header[] = "<info>connected</info>";
            foreach ($database->getTables() as $table) {
                $grid->addRow(array_merge($header,
                    [$table->getName(), number_format($table->count())]));
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