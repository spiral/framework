<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Commands;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Command;
use Spiral\Files\FilesInterface;

final class CleanCommand extends Command
{
    const NAME        = 'clean:cache';
    const DESCRIPTION = 'Clean application runtime cache';

    /**
     * @param FilesInterface       $files
     * @param DirectoriesInterface $directories
     */
    public function perform(FilesInterface $files, DirectoriesInterface $directories)
    {
        $cacheDirectory = $directories->get('cache');
        if (!$files->exists($cacheDirectory)) {
            $this->writeln("Cache directory is missing, no cache to be cleaned.");

            return;
        }

        if ($this->isVerbose()) {
            $this->writeln("<info>Cleaning application cache:</info>");
        }

        foreach ($files->getFiles($cacheDirectory) as $filename) {
            try {
                $files->delete($filename);
            } catch (\Throwable $e) {
                $this->sprintf("> <fg=red>errored</fg=red> `%s`: <fg=red>%s</fg=red>\n",
                    $files->relativePath($filename, $cacheDirectory),
                    $e->getMessage()
                );

                continue;
            }

            if ($this->isVerbose()) {
                $this->sprintf(
                    "> <fg=green>deleted</fg=green> `%s`\n",
                    $files->relativePath($filename, $cacheDirectory)
                );
            }
        }

        $this->writeln("<info>Runtime cache has been cleared.</info>");
    }
}