<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Views;

use Spiral\Console\Command;
use Spiral\Views\Configs\ViewsConfig;

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
     * @param ViewsConfig $config
     */
    public function perform(ViewsConfig $config)
    {
        if (!$this->files->exists($config->cacheDirectory())) {
            $this->writeln("Cache directory is missing, no cache to be cleaned.");

            return;
        }

        $this->isVerbosity() && $this->writeln("<info>Clearing view cache:</info>");

        $cachedViews = $this->files->getFiles($config->cacheDirectory());
        foreach ($cachedViews as $filename) {
            $this->files->delete($filename);
            if ($this->isVerbosity()) {
                $this->writeln($this->files->relativePath($filename, $config->cacheDirectory()));
            }
        }

        (empty($filename) && $this->isVerbosity()) && $this->writeln("No cached views were found.");
        $this->writeln("<info>Cache is cleared.</info>");
    }
}