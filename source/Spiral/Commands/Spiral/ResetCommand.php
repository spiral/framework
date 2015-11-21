<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;

/**
 * Remove every file in cache directory or emulate removal.
 */
class ResetCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'app:reset';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Reset application runtime cache and invalidate configs.';

    /**
     * Perform command.
     */
    public function perform()
    {
        if (!$this->files->exists(directory('cache'))) {
            $this->writeln("Cache directory is missing, no cache to be cleaned.");

            return;
        }

        $this->isVerbosity() && $this->writeln("<info>Clearing application runtime cache:</info>");

        foreach ($this->files->getFiles(directory('cache')) as $filename) {
            $this->files->delete($filename);

            if ($this->isVerbosity()) {
                $this->writeln($this->files->relativePath($filename, directory('cache')));
            }
        }

        $this->writeln("<info>Runtime cache has been cleared.</info>");
    }
}