<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Views;

use Spiral\Console\Command;
use Spiral\Files\FilesInterface;
use Spiral\Views\Configs\ViewsConfig;

/**
 * Remove every file located in view cache directory.
 */
class ResetCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'views:reset';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Clear view cache for all environments';

    /**
     * @param ViewsConfig    $config
     * @param FilesInterface $files
     */
    public function perform(ViewsConfig $config, FilesInterface $files)
    {
        if (!$files->exists($config->cacheDirectory())) {
            $this->writeln("Cache directory is missing, no cache to be cleaned.");

            return;
        }

        $this->isVerbosity() && $this->writeln("<info>Clearing view cache:</info>");

        $cachedViews = $files->getFiles($config->cacheDirectory());
        foreach ($cachedViews as $filename) {
            $files->delete($filename);
            if ($this->isVerbosity()) {
                $this->writeln($files->relativePath($filename, $config->cacheDirectory()));
            }
        }

        if (empty($filename) && $this->isVerbosity()) {
            $this->writeln("No cached views were found.");
        }

        $this->writeln("<info>View cache has been cleared.</info>");
    }
}