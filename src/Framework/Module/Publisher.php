<?php

declare(strict_types=1);

namespace Spiral\Module;

use Spiral\Files\FilesInterface;
use Spiral\Module\Exception\PublishException;
use Symfony\Component\Finder\Finder;

/**
 * Published files and directories.
 */
final class Publisher implements PublisherInterface
{
    public function __construct(
        private readonly FilesInterface $files
    ) {
    }

    public function publish(
        string $filename,
        string $destination,
        string $mergeMode = self::FOLLOW,
        int $mode = FilesInterface::READONLY
    ): void {
        if (!$this->files->isFile($filename)) {
            throw new PublishException(\sprintf("Given '%s' is not valid file", $filename));
        }

        if ($this->files->exists($destination)) {
            if ($this->files->md5($destination) === $this->files->md5($filename)) {
                //Nothing to do
                return;
            }

            if ($mergeMode === self::FOLLOW) {
                return;
            }
        }

        $this->ensureDirectory(\dirname($destination), $mode);

        $this->files->copy($filename, $destination);
        $this->files->setPermissions($destination, $mode);

        \clearstatcache();
    }

    public function publishDirectory(
        string $directory,
        string $destination,
        string $mergeMode = self::REPLACE,
        int $mode = FilesInterface::READONLY
    ): void {
        if (!$this->files->isDirectory($directory)) {
            throw new PublishException(\sprintf("Given '%s' is not valid directory", $directory));
        }

        $finder = new Finder();
        $finder->files()->in($directory);

        foreach ($finder->getIterator() as $file) {
            $this->publish(
                (string)$file,
                $destination . '/' . $file->getRelativePathname(),
                $mergeMode,
                $mode
            );
        }
    }

    public function ensureDirectory(string $directory, int $mode = FilesInterface::READONLY): void
    {
        $this->files->ensureDirectory($directory, $mode);
    }
}
