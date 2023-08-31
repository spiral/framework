<?php

declare(strict_types=1);

namespace Spiral\Storage\Bucket;

use JetBrains\PhpStorm\ExpectedValues;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\FileInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\Visibility;

/**
 * @mixin WritableInterface
 */
trait WritableTrait
{
    public function create(string $pathname, array $config = []): FileInterface
    {
        if ($this instanceof ReadableInterface && !$this->exists($pathname)) {
            return $this->write($pathname, '', $config);
        }

        return $this->file($pathname);
    }

    public function write(string $pathname, mixed $content, array $config = []): FileInterface
    {
        \assert(\is_resource($content) || $this->isStringable($content));

        $fs = $this->getOperator();

        try {
            switch (true) {
                case \is_object($content):
                case \is_string($content):
                    $fs->write($pathname, (string)$content, $config);
                    break;

                case \is_resource($content):
                    $fs->writeStream($pathname, $content, $config);
                    break;

                default:
                    $message = 'Content must be a resource stream or stringable type, but %s passed';
                    throw new \InvalidArgumentException(\sprintf($message, \get_debug_type($content)));
            }
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->file($pathname);
    }

    public function setVisibility(
        string $pathname,
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface {
        $fs = $this->getOperator();

        try {
            $fs->setVisibility($pathname, $this->toFlysystemVisibility($visibility));
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->file($pathname);
    }

    public function copy(
        string $source,
        string $destination,
        BucketInterface $storage = null,
        array $config = []
    ): FileInterface {
        $fs = $this->getOperator();

        if ($storage === null || $storage === $this) {
            try {
                $fs->copy($source, $destination, $config);
            } catch (FilesystemException $e) {
                throw new FileOperationException($e->getMessage(), $e->getCode(), $e);
            }

            return $this->file($destination);
        }

        return $storage->write($destination, $this->getStream($source), $config);
    }

    public function move(
        string $source,
        string $destination,
        BucketInterface $storage = null,
        array $config = []
    ): FileInterface {
        $fs = $this->getOperator();

        if ($storage === null || $storage === $this) {
            try {
                $fs->move($source, $destination, $config);
            } catch (FilesystemException $e) {
                throw new FileOperationException($e->getMessage(), $e->getCode(), $e);
            }

            return $this->file($destination);
        }

        $result = $storage->write($destination, $this->getStream($source), $config);

        $fs->delete($source);

        return $result;
    }

    public function delete(string $pathname, bool $clean = false): void
    {
        $fs = $this->getOperator();

        try {
            $fs->delete($pathname);

            if ($clean) {
                /** @psalm-suppress InternalMethod */
                $this->deleteEmptyDirectories($this->getParentDirectory($pathname));
            }
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), $e->getCode(), $e);
        }
    }
    abstract protected function getOperator(): FilesystemOperator;

    #[ExpectedValues(valuesFromClass: \League\Flysystem\Visibility::class)]
    private function toFlysystemVisibility(
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): string {
        return ($visibility === Visibility::VISIBILITY_PUBLIC)
            ? \League\Flysystem\Visibility::PUBLIC
            : \League\Flysystem\Visibility::PRIVATE;
    }

    /**
     * Internal helper method that returns directory name of passed path.
     *
     * Please note that the use of the PHP {@see \dirname()} function depends
     * on the operating system and it MAY NOT return correct parent directory
     * in the case of slash character (`/` or `\`) incompatible with the
     * current runtime.
     *
     * @internal This is an internal method, please do not use it in your code.
     * @psalm-internal Spiral\Storage\Storage
     */
    private function getParentDirectory(string $path): string
    {
        return \dirname(\str_replace(['\\', '/'], \DIRECTORY_SEPARATOR, $path));
    }

    /**
     * Internal helper method that returns bool {@see true} if the passed
     * directory is the root for the file.
     *
     * @internal This is an internal method, please do not use it in your code.
     * @psalm-internal Spiral\Storage\Storage
     */
    private function hasParentDirectory(string $directory): bool
    {
        return $directory !== '' && $directory !== '.';
    }

    /**
     * Internal helper method that recursively deletes empty directories.
     *
     * @internal This is an internal method, please do not use it in your code.
     * @psalm-internal Spiral\Storage\Storage
     * @psalm-suppress InternalMethod
     *
     * @throws FileOperationException
     */
    private function deleteEmptyDirectories(string $directory): void
    {
        if (!$this->hasParentDirectory($directory)) {
            return;
        }

        $fs = $this->getOperator();

        try {
            if (!$this->hasFiles($directory)) {
                $fs->deleteDirectory($directory);
                $this->deleteEmptyDirectories($this->getParentDirectory($directory));
            }
        } catch (FilesystemException $e) {
            throw new FileOperationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Internal helper method that returns bool {@see true} if directory
     * not empty.
     *
     * Note: Be careful, this method can be quite slow as it asks for a
     * list of files from filesystem.
     *
     * @internal This is an internal method, please do not use it in your code.
     * @psalm-internal Spiral\Storage\Storage
     *
     * @throws FilesystemException
     */
    private function hasFiles(string $directory): bool
    {
        $fs = $this->getOperator();

        foreach ($fs->listContents($directory) as $_) {
            return true;
        }

        return false;
    }

    /**
     * Internal helper method that returns bool {@see true} if passed argument
     * can be converted to string.
     */
    private function isStringable(mixed $value): bool
    {
        return match (true) {
            \is_string($value) => true,
            !\is_object($value) => false,
            default => $value instanceof \Stringable,
        };
    }
}
