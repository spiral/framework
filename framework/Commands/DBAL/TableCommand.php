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
use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\DBALException;
use Spiral\Components\DBAL\SqlFragment;
use Symfony\Component\Console\Input\InputArgument;

class TableCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'dbal:table';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'View table schema of specific database.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments() method.
     *
     * @var array
     */
    protected $arguments = array(
        ['db', InputArgument::REQUIRED, 'Database name.'],
        ['table', InputArgument::REQUIRED, 'Table name.']
    );

    /**
     * Get information about one specific table.
     *
     * @throws DBALException
     */
    public function perform()
    {
        $table = $this->dbal->db($this->argument('db'))->table($this->argument('table'))->schema();
        if (!$table->isExists())
        {
            throw new DBALException("Table {$this->argument('db')}.{$this->argument('table')} does not exists.");
        }

        $this->writeln("Columns of <comment>{$this->argument('db')}.{$this->argument('table')}</comment>:");
        $grid = $this->table(array('Column:', 'Database Type:', 'Abstract Type:', 'PHP Type:', 'Default Value:'));

        foreach ($table->getColumns() as $column)
        {
            $name = $column->getName();
            $type = $column->getType();
            $abstractType = $column->abstractType();
            $defaultValue = $column->getDefaultValue();

            if ($column->getSize())
            {
                $type .= " ({$column->getSize()})";
            }

            if ($column->abstractType() == 'decimal')
            {
                $type .= " ({$column->getPrecision()},{$column->getScale()})";
            }

            if (in_array($column->getName(), $table->getPrimaryKeys()))
            {
                $name = "<fg=magenta>{$name}</fg=magenta>";
            }

            if (in_array($abstractType, array('primary', 'bigPrimary')))
            {
                $abstractType = "<fg=magenta>{$abstractType}</fg=magenta>";
            }

            if ($defaultValue instanceof SqlFragment)
            {
                $defaultValue = "<info>{$defaultValue}</info>";
            }

            $grid->addRow(array($name, $type, $abstractType, $column->phpType(), $defaultValue ?: "<comment>---</comment>"));
        }

        $grid->render();

        if ($table->getIndexes())
        {
            $this->writeln("\nIndexes of <comment>{$this->argument('db')}.{$this->argument('table')}</comment>:");

            $grid = $this->table(array('Name:', 'Type:', 'Columns:'));
            foreach ($table->getIndexes() as $index)
            {
                $grid->addRow(array(
                    $index->getName(),
                    $index->isUnique() ? 'UNIQUE INDEX' : 'INDEX',
                    join(", ", $index->getColumns())
                ));
            }
            $grid->render();
        }
        if ($table->getForeigns())
        {
            $this->writeln("\nForeign keys of <comment>{$this->argument('db')}.{$this->argument('table')}</comment>:");

            $grid = $this->table(array('Name:', 'Column:', 'Foreign Table:', 'Foreign Column:', 'On Delete:', 'On Update:'));
            foreach ($table->getForeigns() as $reference)
            {
                $grid->addRow(array(
                    $reference->getName(),
                    $reference->getColumn(),
                    $reference->getForeignTable(),
                    $reference->getForeignKey(),
                    $reference->getDeleteRule(),
                    $reference->getUpdateRule()
                ));
            }
            $grid->render();
        }
    }
}