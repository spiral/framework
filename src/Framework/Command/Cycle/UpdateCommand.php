<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Command\Cycle;

use Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\Cycle\SchemaBootloader;
use Spiral\Console\Command;

final class UpdateCommand extends Command
{
    protected const NAME        = 'cycle';
    protected const DESCRIPTION = 'Update (init) cycle schema from database and annotated classes';

    /**
     * @param SchemaBootloader $bootloader
     * @param Registry         $registry
     * @param MemoryInterface  $memory
     */
    public function perform(
        SchemaBootloader $bootloader,
        Registry $registry,
        MemoryInterface $memory
    ): void {
        $this->write('Updating ORM schema... ');

        $schema = (new Compiler())->compile($registry, $bootloader->getGenerators());
        $memory->saveData('cycle', $schema);

        $this->writeln('<info>done</info>');
    }
}
