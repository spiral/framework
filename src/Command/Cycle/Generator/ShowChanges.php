<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Command\Cycle\Generator;

use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Spiral\Database\Schema\AbstractTable;
use Symfony\Component\Console\Output\OutputInterface;

final class ShowChanges implements GeneratorInterface
{
    /** @var OutputInterface */
    private $output;

    /** @var array */
    private $changes = [];

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param Registry $registry
     * @return Registry
     */
    public function run(Registry $registry): Registry
    {
        $this->output->writeln("<info>Detecting schema changes:</info>");

        $this->changes = [];
        foreach ($registry->getIterator() as $e) {
            if ($registry->hasTable($e)) {
                $table = $registry->getTableSchema($e);

                if ($table->getComparator()->hasChanges()) {
                    $this->changes[] = [
                        'database' => $registry->getDatabase($e),
                        'table'    => $registry->getTable($e),
                        'schema'   => $table,
                    ];
                }
            }
        }

        if ($this->changes === []) {
            $this->output->writeln("<fg=yellow>no database changes has been detected</fg=yellow>");

            return $registry;
        }

        foreach ($this->changes as $change) {
            $this->output->write(sprintf("• <fg=cyan>%s.%s</fg=cyan>", $change['database'], $change['table']));
            $this->describeChanges($change['schema']);
        }

        return $registry;
    }

    /**
     * @param AbstractTable $table
     */
    protected function describeChanges(AbstractTable $table)
    {
        if (!$this->output->isVerbose()) {
            $this->output->writeln(sprintf(
                ": <fg=green>%s</fg=green> change(s) detected",
                $this->numChanges($table)
            ));

            return;
        } else {
            $this->output->write("\n");
        }

        if (!$table->exists()) {
            $this->output->writeln("    - create table");
        }

        if ($table->getStatus() === AbstractTable::STATUS_DECLARED_DROPPED) {
            $this->output->writeln("    - drop table");
            return;
        }

        $cmp = $table->getComparator();

        foreach ($cmp->addedColumns() as $column) {
            $this->output->writeln("    - add column <fg=yellow>{$column->getName()}</fg=yellow>");
        }

        foreach ($cmp->droppedColumns() as $column) {
            $this->output->writeln("    - drop column <fg=yellow>{$column->getName()}</fg=yellow>");
        }

        foreach ($cmp->alteredColumns() as $column) {
            $this->output->writeln("    - alter column <fg=yellow>{$column->getName()}</fg=yellow>");
        }

        foreach ($cmp->addedIndexes() as $index) {
            $index = join(', ', $index->getColumns());
            $this->output->writeln("    - add index on <fg=yellow>[{$index}]</fg=yellow>");
        }

        foreach ($cmp->droppedIndexes() as $index) {
            $this->output->writeln("    - drop index on <fg=yellow>[{$index}]</fg=yellow>");
        }

        foreach ($cmp->alteredIndexes() as $index) {
            $this->output->writeln("    - alter index on <fg=yellow>[{$index}]</fg=yellow>");
        }

        foreach ($cmp->addedForeignKeys() as $fk) {
            $this->output->writeln("    - add foreign key on <fg=yellow>{$fk->getColumn()}</fg=yellow>");
        }

        foreach ($cmp->droppedForeignKeys() as $fk) {
            $this->output->writeln("    - drop foreign key <fg=yellow>{$fk->getColumn()}</fg=yellow>");
        }

        foreach ($cmp->alteredForeignKeys() as $fk) {
            $this->output->writeln("    - alter foreign key <fg=yellow>{$fk->getColumn()}</fg=yellow>");
        }
    }

    /**
     * @param AbstractTable $table
     * @return int
     */
    protected function numChanges(AbstractTable $table): int
    {

//        if (!$table->exists()) {
//            return 1;
//        }
//
//        if ($table->getStatus() === AbstractTable::STATUS_DECLARED_DROPPED) {
//            return 1;
//        }

        $cmp = $table->getComparator();

        return
            +count($cmp->addedColumns())
            + count($cmp->droppedColumns())
            + count($cmp->alteredColumns())
            + count($cmp->addedIndexes())
            + count($cmp->droppedIndexes())
            + count($cmp->alteredIndexes())
            + count($cmp->addedForeignKeys())
            + count($cmp->droppedForeignKeys())
            + count($cmp->alteredForeignKeys());
    }

    /**
     * @return bool
     */
    public function hasChanges(): bool
    {
        return $this->changes !== [];
    }
}