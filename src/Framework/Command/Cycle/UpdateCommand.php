<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Command\Cycle;

use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\Cycle\SchemaBootloader;
use Spiral\Console\Command;
use Spiral\Cycle\SchemaCompiler;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class UpdateCommand extends Command
{
    protected const NAME        = 'cycle';
    protected const DESCRIPTION = 'Update (init) cycle schema from database and annotated classes';

    /**
     * @param SchemaBootloader $bootloader
     * @param Registry         $registry
     * @param MemoryInterface  $memory
     * @throws \Throwable
     */
    public function perform(
        SchemaBootloader $bootloader,
        Registry $registry,
        MemoryInterface $memory
    ): void {
        $this->write('Updating ORM schema... ');

        $schemaCompiler = SchemaCompiler::compile($registry, $bootloader->getGenerators());
        $schemaCompiler->toMemory($memory);

        $this->writeln('<info>done</info>');
    }
}
