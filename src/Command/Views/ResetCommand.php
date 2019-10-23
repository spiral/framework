<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Command\Views;

use Spiral\Console\Command;
use Spiral\Files\FilesInterface;
use Spiral\Views\Config\ViewsConfig;

/**
 * Remove every file located in view cache directory.
 */
final class ResetCommand extends Command
{
    protected const NAME        = 'views:reset';
    protected const DESCRIPTION = 'Clear view cache';

    /**
     * @param ViewsConfig    $config
     * @param FilesInterface $files
     */
    public function perform(ViewsConfig $config, FilesInterface $files): void
    {
        if (!$files->exists($config->getCacheDirectory())) {
            $this->writeln('Cache directory is missing, no cache to be cleaned.');

            return;
        }

        if ($this->isVerbose()) {
            $this->writeln('<info>Cleaning view cache:</info>');
        }

        foreach ($files->getFiles($config->getCacheDirectory()) as $filename) {
            try {
                $files->delete($filename);
            } catch (\Throwable $e) {
                // @codeCoverageIgnoreStart
                $this->sprintf(
                    "<fg=red>[errored]</fg=red> `%s`: <fg=red>%s</fg=red>\n",
                    $files->relativePath($filename, $config->getCacheDirectory()),
                    $e->getMessage()
                );

                continue;

                // @codeCoverageIgnoreEnd
            }

            if ($this->isVerbose()) {
                $this->sprintf(
                    "<fg=green>[deleted]</fg=green> `%s`\n",
                    $files->relativePath($filename, $config->getCacheDirectory())
                );
            }
        }

        $this->writeln('<info>View cache has been cleared.</info>');
    }
}