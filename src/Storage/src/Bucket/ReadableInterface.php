<?php

declare(strict_types=1);

namespace Spiral\Storage\Bucket;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 */
interface ReadableInterface
{
    /**
     * Reads file by relative pathname and return its content as string.
     *
     * @throws FileOperationException
     */
    public function getContents(string $pathname): string;

    /**
     * Reads file by relative pathname and return its content as resource stream.
     *
     * @return resource
     * @throws FileOperationException
     */
    public function getStream(string $pathname);

    /**
     * Checks file for existing.
     *
     * @throws FileOperationException
     */
    public function exists(string $pathname): bool;

    /**
     * Returns the timestamp of last file modification.
     *
     * @return positive-int
     * @throws FileOperationException
     */
    public function getLastModified(string $pathname): int;

    /**
     * Returns the file size in bytes.
     *
     * @return positive-int|0
     * @throws FileOperationException
     */
    public function getSize(string $pathname): int;

    /**
     * Returns the file mime type.
     *
     * @throws FileOperationException
     */
    public function getMimeType(string $pathname): string;

    /**
     * Returns the file visibility.
     *
     * @return VisibilityType
     * @throws FileOperationException
     */
    #[ExpectedValues(valuesFromClass: Visibility::class)]
    public function getVisibility(string $pathname): string;
}
