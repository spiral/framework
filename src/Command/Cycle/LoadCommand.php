<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Cycle;

use Cycle\Annotated\Columns;
use Cycle\Annotated\Entities;
use Cycle\Annotated\Indexes;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\Registry;
use Spiral\Console\Command;
use Spiral\Database\DatabaseManager;
use Spiral\Tokenizer\ClassesInterface;

final class LoadCommand extends Command
{
    public const NAME        = "cycle:load";
    public const DESCRIPTION = "Load (init) cycle schema from database and annotated classes";

    /**
     * @param DatabaseManager  $dbal
     * @param MemoryInterface  $memory
     * @param ClassesInterface $classes
     */
    public function perform(
        DatabaseManager $dbal,
        MemoryInterface $memory,
        ClassesInterface $classes
    ) {
        $registry = new Registry($dbal);

        $schema = (new Compiler())->compile($registry, [
            new Entities($classes),
            new CleanTables(),
            new Columns(),
            new GenerateRelations(),
            new ValidateEntities(),
            new RenderTables(),
            new RenderRelations(),
            new Indexes(),
            new SyncTables(),
            new GenerateTypecast(),
        ]);

        $memory->saveData("orm", $schema);
        dump($schema);

        $this->write("<info>ORM Schema has been updated</info>");
    }
}