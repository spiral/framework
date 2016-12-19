<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Database;

use Spiral\Console\Command;
use Spiral\Database\DatabaseManager;
use Spiral\Database\Exceptions\DatabaseException;
use Spiral\Database\Injections\FragmentInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Describe schema of specific table.
 */
class DescribeCommand extends Command
{
    /**
     * No information available placeholder.
     */
    const SKIP = '<comment>---</comment>';

    /**
     * {@inheritdoc}
     */
    const NAME = 'db:describe';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Describe table schema of specific database';

    /**
     * {@inheritdoc}
     */
    const ARGUMENTS = [
        ['table', InputArgument::REQUIRED, 'Table name']
    ];

    /**
     * {@inheritdoc}
     */
    const OPTIONS = [
        ['database', 'db', InputOption::VALUE_OPTIONAL, 'Source database', 'default'],
    ];

    /**
     * @param DatabaseManager $dbal
     */
    public function perform(DatabaseManager $dbal)
    {
        //Database
        $database = $dbal->database($this->option('database'));

        //Database schema
        $schema = $database->table($this->argument('table'))->getSchema();

        if (!$schema->exists()) {
            throw new DatabaseException(
                "Table {$database->getName()}.{$this->argument('table')} does not exists."
            );
        }

        $this->writeln(
            "Columns of <comment>{$database->getName()}.{$this->argument('table')}</comment>:"
        );

        $columnsTable = $this->table([
            'Column:',
            'Database Type:',
            'Abstract Type:',
            'PHP Type:',
            'Default Value:'
        ]);

        foreach ($schema->getColumns() as $column) {
            $name = $column->getName();
            $type = $column->getType();

            $abstractType = $column->abstractType();
            $defaultValue = $column->getDefaultValue();

            if ($column->getSize()) {
                $type .= " ({$column->getSize()})";
            }

            if ($column->abstractType() == 'decimal') {
                $type .= " ({$column->getPrecision()}, {$column->getScale()})";
            }

            if (in_array($column->getName(), $schema->getPrimaryKeys())) {
                $name = "<fg=magenta>{$name}</fg=magenta>";
            }

            if (in_array($abstractType, ['primary', 'bigPrimary'])) {
                $abstractType = "<fg=magenta>{$abstractType}</fg=magenta>";
            }

            if ($defaultValue instanceof FragmentInterface) {
                $defaultValue = "<info>{$defaultValue}</info>";
            }

            if ($defaultValue instanceof \DateTimeInterface) {
                $defaultValue = $defaultValue->format('c');
            }

            $columnsTable->addRow([
                $name,
                $type,
                $abstractType,
                $column->phpType(),
                $defaultValue ?: self::SKIP
            ]);
        }

        $columnsTable->render();

        if (!empty($indexes = $schema->getIndexes())) {
            $this->writeln(
                "\nIndexes of <comment>{$database->getName()}.{$this->argument('table')}</comment>:"
            );

            $indexesTable = $this->table(['Name:', 'Type:', 'Columns:']);
            foreach ($indexes as $index) {
                $indexesTable->addRow([
                    $index->getName(),
                    $index->isUnique() ? 'UNIQUE INDEX' : 'INDEX',
                    join(", ", $index->getColumns())
                ]);
            }
            $indexesTable->render();
        }

        if (!empty($foreigns = $schema->getForeigns())) {
            $this->writeln(
                "\nForeign keys of <comment>{$database->getName()}.{$this->argument('table')}</comment>:"
            );

            $foreignsTable = $this->table([
                'Name:',
                'Column:',
                'Foreign Table:',
                'Foreign Column:',
                'On Delete:',
                'On Update:'
            ]);

            foreach ($foreigns as $reference) {
                $foreignsTable->addRow([
                    $reference->getName(),
                    $reference->getColumn(),
                    $reference->getForeignTable(),
                    $reference->getForeignKey(),
                    $reference->getDeleteRule(),
                    $reference->getUpdateRule()
                ]);
            }
            $foreignsTable->render();
        }
    }
}