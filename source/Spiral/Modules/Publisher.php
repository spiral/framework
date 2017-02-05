<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Modules;

use Spiral\Core\Component;
use Spiral\Core\DirectoriesInterface;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Files\FilesInterface;
use Spiral\Modules\Exceptions\PublishException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Published files and directories.
 */
class Publisher extends Component implements PublisherInterface
{
    use LoggerTrait;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @var DirectoriesInterface
     */
    protected $directories = null;

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

        //For logs
        $relativeFilename = $this->files->normalizePath(
            $this->files->relativePath($filename, $this->directories->directory('root'))
        );

        //For logs
        $relativeDestination = $this->files->relativePath(
            $destination,
            $this->directories->directory('root')
        );

        if ($this->files->exists($destination)) {
            if ($this->files->md5($destination) == $this->files->md5($filename)) {
                $this->logger()->debug(
                    "File '{relativeFilename}' already published and latest version.",
                    compact('relativeFilename', 'destination')
                );

                //Nothing to do
                return;
            }

            if ($merge == self::FOLLOW) {
                //We are not allowed to replace file
                $this->logger()->warning(
                    "File '{relativeFilename}' already published and can not be replaced.",
                    compact('relativeFilename', 'destination')
                );

                return;
            }
        }

        $this->logger()->info(
            "Publish file '{relativeFilename}' to '{relativeDestination}'.",
            compact('relativeFilename', 'relativeDestination')
        );

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
        bool $merge = self::OVERWRITE,
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
        $this->logger()->info("Ensure directory '{directory}'.", compact('directory'));
        $this->files->ensureDirectory($directory, $mode);
    }
}
