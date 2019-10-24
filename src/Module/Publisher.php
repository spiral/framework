<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Module;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;
use Spiral\Module\Exception\PublishException;
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
    private $dirs = null;

    /**
     * @param FilesInterface       $files
     * @param DirectoriesInterface $dirs
     */
    public function __construct(FilesInterface $files, DirectoriesInterface $dirs)
    {
        $this->files = $files;
        $this->dirs = $dirs;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(
        string $filename,
        string $destination,
        string $mergeMode = self::FOLLOW,
        int $mode = FilesInterface::READONLY
    ): void {
        if (!$this->files->isFile($filename)) {
            throw new PublishException("Given '{$filename}' is not valid file");
        }

        if ($this->files->exists($destination)) {
            if ($this->files->md5($destination) == $this->files->md5($filename)) {
                //Nothing to do
                return;
            }

            if ($mergeMode == self::FOLLOW) {
                return;
            }
        }

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
        string $mergeMode = self::REPLACE,
        int $mode = FilesInterface::READONLY
    ): void {
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
                $mergeMode,
                $mode
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ensureDirectory(string $directory, int $mode = FilesInterface::READONLY): void
    {
        $this->files->ensureDirectory($directory, $mode);
    }
}
