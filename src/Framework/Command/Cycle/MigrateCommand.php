<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Command\Cycle;

use Cycle\Migrations\GenerateMigrations;
use Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\Cycle\SchemaBootloader;
use Spiral\Command\Cycle\Generator\ShowChanges;
use Spiral\Command\Migrate\AbstractCommand;
use Spiral\Console\Console;
use Spiral\Cycle\SchemaCompiler;
use Spiral\Migrations\Migration\Status;
use Spiral\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class MigrateCommand extends AbstractCommand
{
    protected const NAME        = 'cycle:migrate';
    protected const DESCRIPTION = 'Generate ORM schema migrations';
    protected const OPTIONS     = [
        ['run', 'r', InputOption::VALUE_NONE, 'Automatically run generated migration.'],
    ];

    /**
     * @param SchemaBootloader   $bootloader
     * @param Registry           $registry
     * @param MemoryInterface    $memory
     * @param GenerateMigrations $migrations
     * @param Migrator           $migrator
     * @param Console            $console
     *
     * @throws \Throwable
     */
    public function perform(
        SchemaBootloader $bootloader,
        Registry $registry,
        MemoryInterface $memory,
        GenerateMigrations $migrations,
        Migrator $migrator,
        Console $console
    ): void {
        $migrator->configure();

        foreach ($migrator->getMigrations() as $migration) {
            if ($migration->getState()->getStatus() !== Status::STATUS_EXECUTED) {
                $this->writeln('<fg=red>Outstanding migrations found, run `migrate` first.</fg=red>');
                return;
            }
        }

        $show = new ShowChanges($this->output);

        $schemaCompiler = SchemaCompiler::compile(
            $registry,
            array_merge($bootloader->getGenerators(), [$show])
        );
        $schemaCompiler->toMemory($memory);

        if ($show->hasChanges()) {
            (new Compiler())->compile($registry, [$migrations]);

            if ($this->option('run')) {
                $console->run('migrate', [], $this->output);
            }
        }
    }
}
