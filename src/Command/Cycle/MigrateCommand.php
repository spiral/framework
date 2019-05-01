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
use Spiral\Console\Command;
use Spiral\Console\Console;

final class MigrateCommand extends Command
{
    public const NAME        = "cycle:migrate";
    public const DESCRIPTION = "Generate ORM schema migrations";

    /**
     * @param SchemaBootloader   $bootloader
     * @param Registry           $registry
     * @param MemoryInterface    $memory
     * @param GenerateMigrations $migrations
     * @param Console            $console
     */
    public function perform(
        SchemaBootloader $bootloader,
        Registry $registry,
        MemoryInterface $memory,
        GenerateMigrations $migrations,
        Console $console
    ) {
        $show = new ShowChanges($this->output);

        $schema = (new Compiler())->compile(
            $registry,
            array_merge($bootloader->getGenerators(), [$show])
        );

        $memory->saveData('cycle', $schema);

        if ($show->hasChanges()) {
            (new Compiler())->compile($registry, [$migrations]);

            $console->run('migrate', [], $this->output);
        }
    }
}