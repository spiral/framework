<?php

declare(strict_types=1);

namespace Spiral\Files;

use Spiral\Files\Exception\FileNotFoundException;
use Spiral\Files\Exception\FilesException;
use Spiral\Files\Exception\WriteErrorException;

/**
 * Access to hard drive or local store. Does not provide full filesystem abstractions.
 */
interface FilesInterface
{
    //Owner and group can write
    public const RUNTIME = 0666;

    //Only owner can write
    public const READONLY = 0644;

    /**
     * Few size constants for better size manipulations.
     */
    public const KB = 1024;

    public const MB = 1048576;
    public const GB = 1073741824;

    /**
     * Default location (directory) separator.
     */
    public const SEPARATOR = '/';

    /**
     * Ensure location (directory) existence with specified mode.
     *
     * @param int $mode When NULL class can pick default mode.
     */
    public function ensureDirectory(string $directory, ?int $mode = null): bool;

    /**
     * Read file content into string.
     *
     * @param non-empty-string $filename
     *
     * @throws FileNotFoundException
     * @throws FilesException When unable to read file.
     */
    public function read(string $filename): string;

    /**
     * Write file data with specified mode. Ensure location option should be used only if desired
     * location may not exist to ensure such location/directory (slow operation).
     *
     * @param int  $mode            When NULL class can pick default mode.
     * @param bool $ensureDirectory Ensure final destination!
     *
     * @throws WriteErrorException
     */
    public function write(
        string $filename,
        string $data,
        ?int $mode = null,
        bool $ensureDirectory = false,
    ): bool;

    /**
     * Same as write method with will append data at the end of existed file without replacing it.
     *
     * @see write()
     *
     * @param int $mode When NULL class can pick default mode.
     *
     * @throws WriteErrorException
     */
    public function append(
        string $filename,
        string $data,
        ?int $mode = null,
        bool $ensureDirectory = false,
    ): bool;

    /**
     * Delete local file if possible. No error should be raised if file does not exists.
     */
    public function delete(string $filename): bool;

    /**
     * Delete directory all content in it.
     */
    public function deleteDirectory(string $directory, bool $contentOnly = false): bool;

    /**
     * Move file from one location to another. Location must exist.
     *
     * @throws FileNotFoundException
     */
    public function move(string $filename, string $destination): bool;

    /**
     * Copy file at new location. Location must exist.
     *
     * @throws FileNotFoundException
     */
    public function copy(string $filename, string $destination): bool;

    /**
     * Touch file to update it's timeUpdated value or create new file. Location must exist.
     *
     * @param int $mode When NULL class can pick default mode.
     */
    public function touch(string $filename, ?int $mode = null): bool;

    /**
     * Check if file exists.
     */
    public function exists(string $filename): bool;

    /**
     * Get filesize in bytes if file does exists.
     *
     * @throws FileNotFoundException
     */
    public function size(string $filename): int;

    /**
     * Get file extension using it's name. Simple but pretty common method.
     */
    public function extension(string $filename): string;

    /**
     * Get file MD5 hash.
     *
     * @param non-empty-string $filename
     *
     * @throws FileNotFoundException
     * @throws FilesException When unable to get file hash.
     */
    public function md5(string $filename): string;

    /**
     * Timestamp when file being updated/created.
     *
     * @param non-empty-string $filename
     *
     * @throws FileNotFoundException
     * @throws FilesException When unable to get file time.
     */
    public function time(string $filename): int;

    /**
     * @param non-empty-string $filename
     */
    public function isDirectory(string $filename): bool;

    /**
     * @param non-empty-string $filename
     */
    public function isFile(string $filename): bool;

    /**
     * Current file permissions (if exists).
     *
     * @param non-empty-string $filename
     * @return int<0, 511>
     *
     * @throws FileNotFoundException
     * @throws FilesException When unable to get file permissions.
     */
    public function getPermissions(string $filename): int;

    /**
     * Update file permissions.
     *
     * @param non-empty-string $filename
     * @param int<0, 511> $mode
     *
     * @throws FileNotFoundException
     * @throws FilesException When unable to set file permissions.
     */
    public function setPermissions(string $filename, int $mode): bool;

    /**
     * Flat list of every file in every sub location. Locations must be normalized.
     *
     * Note: not a generator yet, waiting for PHP7.
     *
     * @param non-empty-string $location Location for search.
     * @param non-empty-string|null $pattern Extension pattern.
     *
     * @return list<non-empty-string>
     * /
     */
    public function getFiles(string $location, ?string $pattern = null): array;

    /**
     * Return unique name of temporary (should be removed when interface implementation destructed)
     * file in desired location.
     *
     * @param string $extension Desired file extension.
     *
     * @throws FilesException When unable to create a temporary file.
     */
    public function tempFilename(string $extension = '', ?string $location = null): string;

    /*
     * Move outside in a future versions.
     */
    /**
     * Create the most normalized version for path to file or location.
     *
     * @param string $path        File or location path.
     * @param bool   $asDirectory Path points to directory.
     */
    public function normalizePath(string $path, bool $asDirectory = false): string;

    /**
     * Get relative location based on absolute path.
     *
     * @param string $path Original file or directory location (to).
     * @param string $from Path will be converted to be relative to this directory (from).
     */
    public function relativePath(string $path, string $from): string;
}
