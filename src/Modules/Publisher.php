<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Modules;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;
use Spiral\Modules\Exception\PublishException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Published files and directories.
 */
final class Publisher implements PublisherInterface
{
    /** @var FilesInterface */
    private $files = null;

    /** @var DirectoriesInterface */
    private $directories = null;

    /**
     * @param FilesInterface       $files
     * @param DirectoriesInterface $directories
     */
    public function __construct(FilesInterface $files, DirectoriesInterface $directories)
    {
        $this->files = $files;
        $this->directories = $directories;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(
        string $filename,
        string $destination,
        bool $merge = self::FOLLOW,
        int $mode = FilesInterface::READONLY
    ) {
        if (!$this->files->isFile($filename)) {
            throw new PublishException("Given '{$filename}' is not valid file");
        }

        if ($this->files->exists($destination)) {
            if ($this->files->md5($destination) == $this->files->md5($filename)) {
                //Nothing to do
                return;
            }

            if ($merge == self::FOLLOW) {
                return;
            }
        }

        //File manipulations
        $this->ensureDirectory(dirname($destination), $mode);

        $this->files->copy($filename, $destination);
        $this->files->setPermissions($destination, $mode);

        clearstatcache();
    }

    /**
     * {@inheritdoc}
     */
    public function publishDirectory(
        string $directory,
        string $destination,
        bool $merge = self::REPLACE,
        int $mode = FilesInterface::READONLY
    ) {
        if (!$this->files->isDirectory($directory)) {
            throw new PublishException("Given '{$directory}' is not valid directory");
        }

        $finder = new Finder();
        $finder->files()->in($directory);

        /**
         * @var SplFileInfo $file
         */
        foreach ($finder->getIterator() as $file) {
            $this->publish(
                (string)$file,
                $destination . '/' . $file->getRelativePathname(),
                $merge,
                $mode
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ensureDirectory(string $directory, int $mode = FilesInterface::READONLY)
    {
        $this->files->ensureDirectory($directory, $mode);
    }
}
