<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Views;

use Spiral\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Remove every file located in view cache directory.
 */
class ResetCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'views:reset';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clear view cache for all environments.';

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['emulate', 'e', InputOption::VALUE_NONE, 'Only emulate removal.']
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        $this->isVerbosing() && $this->writeln("<info>Clearing view cache:</info>");

        $cachedViews = $this->files->getFiles($this->views->config()['cache']['directory']);
        foreach ($cachedViews as $filename) {
            !$this->option('emulate') && $this->files->delete($filename);
            $this->isVerbosing() && $this->writeln($this->files->relativePath(
                $filename, directory('cache')
            ));
        }

        (empty($filename) && $this->isVerbosing()) && $this->writeln("No cached views were found.");
        $this->writeln("<info>Cache is cleared.</info>");
    }
}