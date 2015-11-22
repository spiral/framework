<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;
use Spiral\Core\BootloadProcessor;
use Spiral\Core\Core;

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
    protected $description = 'Reload application boot-loading list.';

    /**
     * Perform command.
     */
    public function perform()
    {
        if (!$this->files->exists(directory('cache'))) {
            $this->writeln("Cache directory is missing, no cache to be cleaned.");

            return;
        }
        $this->files->delete(directory('cache') . BootloadProcessor::MEMORY . Core::EXTENSION);
        $this->writeln("<info>Reloading cache has been cleared.</info>");
    }
}