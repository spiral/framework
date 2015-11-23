<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;
use Spiral\Core\DirectoriesInterface;
use Spiral\Files\FilesInterface;

/**
 * Remove every file in cache directory or emulate removal.
 */
class CleanCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'app:clean';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clean application runtime cache.';

    /**
     * @param FilesInterface       $files
     * @param DirectoriesInterface $directories
     */
    public function perform(FilesInterface $files, DirectoriesInterface $directories)
    {
        $cacheDirectory = $directories->directory('cache');

        if (!$files->exists($cacheDirectory)) {
            $this->writeln("Cache directory is missing, no cache to be cleaned.");

            return;
        }

        $this->isVerbosity() && $this->writeln("<info>Cleaning application runtime cache:</info>");

        foreach ($files->getFiles($cacheDirectory) as $filename) {
            $files->delete($filename);

            if ($this->isVerbosity()) {
                $this->writeln(
                    $files->relativePath($filename, $cacheDirectory)
                );
            }
        }

        $this->writeln("<info>Runtime cache has been cleared.</info>");
    }
}