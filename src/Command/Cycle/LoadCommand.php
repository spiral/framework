<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Cycle;

use Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\Cycle\SchemaBootloader;
use Spiral\Console\Command;

final class LoadCommand extends Command
{
    public const NAME        = "cycle:load";
    public const DESCRIPTION = "Load (init) cycle schema from database and annotated classes";

    /**
     * @param SchemaBootloader $bootloader
     * @param Registry         $registry
     * @param MemoryInterface  $memory
     */
    public function perform(SchemaBootloader $bootloader, Registry $registry, MemoryInterface $memory)
    {
        $this->write("Loading ORM schema... ");

        $schema = (new Compiler())->compile($registry, $bootloader->getGenerators());
        $memory->saveData('cycle', $schema);

        $this->writeln("<info>done</info>");
    }
}