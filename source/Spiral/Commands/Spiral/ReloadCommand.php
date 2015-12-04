<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;
use Spiral\Core\HippocampusInterface;

/**
 * Reload application boot-loading list.
 */
class ReloadCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'app:reload';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Reload application boot-loading list';

    /**
     * @param HippocampusInterface $memory
     */
    public function perform(HippocampusInterface $memory)
    {
        $memory->saveData('bootloading', null);
        $this->writeln("<info>Bootload cache has been cleared.</info>");
    }
}