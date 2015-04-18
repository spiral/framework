<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Core;

use Spiral\Components\Console\Command;
use Spiral\Core\Core;

class TouchCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'core:touch';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Touch configuration files to reset their cached state.';

    /**
     * Updating application environment.
     */
    public function perform()
    {
        $configDirectory = $this->file->normalizePath(directory('config'));
        $configs = $this->file->getFiles($configDirectory, substr(Core::CONFIGS_EXTENSION, 1));
        foreach ($configs as $filename)
        {
            $this->file->touch($filename);
        }

        $this->writeln("All config files touched.");
    }
}