<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;
use Spiral\Core\Core;
use Spiral\Core\MemoryInterface;

/**
 * Reload application boot-loading list.
 */
class ReloadCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'app:reload';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Reload application boot-loading list';

    /**
     * @param MemoryInterface $memory
     */
    public function perform(MemoryInterface $memory)
    {
        $memory->saveData(Core::BOOT_MEMORY, null);
        $this->writeln("<info>Bootload cache has been cleared.</info>");
    }
}