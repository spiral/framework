<?php

declare(strict_types=1);

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Cycle;

use Cycle\ORM\Schema;
use Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\Cycle\CycleBootloader;
use Spiral\Bootloader\Cycle\SchemaBootloader;
use Spiral\Console\Command;
use Spiral\Core\Container;

final class UpdateCommand extends Command
{
    protected const NAME        = 'cycle';
    protected const DESCRIPTION = 'Update (init) cycle schema from database and annotated classes';

    /**
     * @param SchemaBootloader $bootloader
     * @param Container        $container
     * @param CycleBootloader  $cycleBootloader
     * @param Registry         $registry
     * @param MemoryInterface  $memory
     */
    public function perform(
        SchemaBootloader $bootloader,
        Container $container,
        CycleBootloader $cycleBootloader,
        Registry $registry,
        MemoryInterface $memory
    ): void {
        $this->write('Updating ORM schema... ');

        $schema = (new Compiler())->compile($registry, $bootloader->getGenerators());
        $memory->saveData('cycle', $schema);

        $this->writeln('<info>done</info>');

        $cycleBootloader->bindRepositories($container, new Schema($schema));
    }
}
