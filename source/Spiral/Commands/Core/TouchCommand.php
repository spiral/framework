<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Core;

use Spiral\Console\Command;
use Spiral\Core\Core;

/**
 * Touch every configuration file to force application reset config cache.
 */
class TouchCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'app:touch';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Touch configuration files to reset their cached state.';

    /**
     * Perform command.
     */
    public function perform()
    {
        foreach ($this->files->getFiles(directory('config'), Core::EXTENSION) as $filename) {
            $this->files->touch($filename);
        }

        $this->writeln("All config files touched.");
    }
}