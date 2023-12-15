<?php

declare(strict_types=1);

namespace Spiral\Files;

use Spiral\Files\Exception\FileNotFoundException;
use Spiral\Files\Exception\FilesException;
use Spiral\Files\Exception\WriteErrorException;

/**
 * Default abstraction for file management operations.
 */
final class Files implements FilesInterface
{
    /**
     * Default file mode for this manager.
     */
    public const DEFAULT_FILE_MODE = self::READONLY;

    /**
     * Files to be removed when component destructed.
     */
    private array $destructFiles = [];

    /**
     * FileManager constructor.
     */
    public function __construct()
    {
        \register_shutdown_function([$this, '__destruct']);
    }

    /**
     * Destruct every temporary file.
     */
    public function __destruct()
    {
        foreach ($this->destructFiles as $filename) {
            $this->delete($filename);
        }
    }

    /**
     * @param bool $recursivePermissions Propagate permissions on created directories.
     */
    public function ensureDirectory(
        string $directory,
        int $mode = null,
        bool $recursivePermissions = true
    ): bool {
        if (empty($mode)) {
            $mode = self::DEFAULT_FILE_MODE;
        }

        //Directories always executable
        $mode |= 0o111;
        if (\is_dir($directory)) {
            //Exists :(
            return $this->setPermissions($directory, $mode);
        }

        if (!$recursivePermissions) {
            return \mkdir($directory, $mode, true);
        }

        $directoryChain = [\basename($directory)];

        $baseDirectory = $directory;
        while (!\is_dir($baseDirectory = \dirname($baseDirectory))) {
            $directoryChain[] = \basename($baseDirectory);
        }

        foreach (\array_reverse($directoryChain) as $dir) {
            if (!mkdir($baseDirectory = \sprintf('%s/%s', $baseDirectory, $dir))) {
                return false;
            }

            \chmod($baseDirectory, $mode);
        }

        return true;
    }

    public function read(string $filename): string
    {
        if (!$this->exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        return \file_get_contents($filename);
    }

    /**
     * @param bool $append To append data at the end of existed file.
     */
    public function write(
        string $filename,
        string $data,
        int $mode = null,
        bool $ensureDirectory = false,
        bool $append = false
    ): bool {
        $mode ??= self::DEFAULT_FILE_MODE;

        try {
            if ($ensureDirectory) {
                $this->ensureDirectory(\dirname($filename), $mode);
            }

            if ($this->exists($filename)) {
                //Forcing mode for existed file
                $this->setPermissions($filename, $mode);
            }

            $result = \file_put_contents(
                $filename,
                $data,
                $append ? FILE_APPEND | LOCK_EX : LOCK_EX
            );

            if ($result !== false) {
                //Forcing mode after file creation
                $this->setPermissions($filename, $mode);
            }
        } catch (\Exception $e) {
            throw new WriteErrorException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return $result !== false;
    }

    public function append(
        string $filename,
        string $data,
        int $mode = null,
        bool $ensureDirectory = false
    ): bool {
        return $this->write($filename, $data, $mode, $ensureDirectory, true);
    }

    public function delete(string $filename): bool
    {
        if ($this->exists($filename)) {
            $result = \unlink($filename);

            //Wiping out changes in local file cache
            \clearstatcache(false, $filename);

            return $result;
        }

        return false;
    }

    /**
     * @see http://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it
     *
     * @throws FilesException
     */
    public function deleteDirectory(string $directory, bool $contentOnly = false): bool
    {
        if (!$this->isDirectory($directory)) {
            throw new FilesException(\sprintf('Undefined or invalid directory %s', $directory));
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                \rmdir($file->getRealPath());
            } else {
                $this->delete($file->getRealPath());
            }
        }

        if (!$contentOnly) {
            return \rmdir($directory);
        }

        return true;
    }

    public function move(string $filename, string $destination): bool
    {
        if (!$this->exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        return \rename($filename, $destination);
    }

    public function copy(string $filename, string $destination): bool
    {
        if (!$this->exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        return \copy($filename, $destination);
    }

    public function touch(string $filename, int $mode = null): bool
    {
        if (!\touch($filename)) {
            return false;
        }

        return $this->setPermissions($filename, $mode ?? self::DEFAULT_FILE_MODE);
    }

    public function exists(string $filename): bool
    {
        return \file_exists($filename);
    }

    public function size(string $filename): int
    {
        if (!$this->exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        return \filesize($filename);
    }

    public function extension(string $filename): string
    {
        return \strtolower(\pathinfo($filename, PATHINFO_EXTENSION));
    }

    public function md5(string $filename): string
    {
        if (!$this->exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        return \md5_file($filename);
    }

    public function time(string $filename): int
    {
        if (!$this->exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        return \filemtime($filename);
    }

    public function isDirectory(string $filename): bool
    {
        return \is_dir($filename);
    }

    public function isFile(string $filename): bool
    {
        return \is_file($filename);
    }

    public function getPermissions(string $filename): int
    {
        if (!$this->exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        return \fileperms($filename) & 0777;
    }

    public function setPermissions(string $filename, int $mode): bool
    {
        if (\is_dir($filename)) {
            //Directories must always be executable (i.e. 664 for dir => 775)
            $mode |= 0111;
        }

        return $this->getPermissions($filename) === $mode || \chmod($filename, $mode);
    }

    public function getFiles(string $location, string $pattern = null): array
    {
        $result = [];
        foreach ($this->filesIterator($location, $pattern) as $filename) {
            if ($this->isDirectory($filename->getPathname())) {
                $result = \array_merge($result, $this->getFiles($filename . DIRECTORY_SEPARATOR));

                continue;
            }

            $result[] = $this->normalizePath((string)$filename);
        }

        return $result;
    }

    public function tempFilename(string $extension = '', string $location = null): string
    {
        if (empty($location)) {
            $location = \sys_get_temp_dir();
        }

        $filename = \tempnam($location, 'spiral');

        if (!empty($extension)) {
            $old = $filename;
            $filename = \sprintf('%s.%s', $filename, $extension);
            \rename($old, $filename);
            $this->destructFiles[] = $filename;
        }

        return $filename;
    }

    public function normalizePath(string $path, bool $asDirectory = false): string
    {
        $isUnc = \str_starts_with($path, '\\\\') || \str_starts_with($path, '//');
        if ($isUnc) {
            $leadingSlashes = \substr($path, 0, 2);
            $path = \substr($path, 2);
        }

        $path = \str_replace(['//', '\\'], '/', $path);

        //Potentially open links and ../ type directories?
        return ($isUnc ? $leadingSlashes : '') . \rtrim($path, '/') . ($asDirectory ? '/' : '');
    }

    /**
     * @link http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
     */
    public function relativePath(string $path, string $from): string
    {
        $path = $this->normalizePath($path);
        $from = $this->normalizePath($from);

        $from = \explode('/', $from);
        $path = \explode('/', $path);
        $relative = $path;

        foreach ($from as $depth => $dir) {
            //Find first non-matching dir
            if ($dir === $path[$depth]) {
                //Ignore this directory
                \array_shift($relative);
            } else {
                //Get number of remaining dirs to $from
                $remaining = \count($from) - $depth;
                if ($remaining > 1) {
                    //Add traversals up to first matching directory
                    $padLength = (\count($relative) + $remaining - 1) * -1;
                    $relative = \array_pad($relative, $padLength, '..');
                    break;
                }
                $relative[0] = './' . $relative[0];
            }
        }

        return \implode('/', $relative);
    }

    private function filesIterator(string $location, string $pattern = null): \GlobIterator
    {
        $pattern ??= '*';
        $regexp = \rtrim($location, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . \ltrim($pattern, DIRECTORY_SEPARATOR);

        return new \GlobIterator($regexp);
    }
}
