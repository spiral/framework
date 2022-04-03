<?php

declare(strict_types=1);

namespace Spiral\Storage\Bucket;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\FileInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 */
interface WritableInterface
{
    /**
     * Creates new empty file.
     *
     * @throws FileOperationException
     */
    public function create(string $pathname, array $config = []): FileInterface;

    /**
     * Write provided content to defined filesystem as file by defined filepath.
     *
     * @param string $pathname relative filepath
     * @param string|\Stringable|resource $content content string or stream to write
     * @param array $config specific config based on used adapter
     * @throws FileOperationException
     */
    public function write(string $pathname, mixed $content, array $config = []): FileInterface;

    /**
     * Sets file visibility.
     *
     * @param VisibilityType $visibility
     * @throws FileOperationException
     */
    public function setVisibility(
        string $pathname,
        #[ExpectedValues(valuesFromClass: Visibility::class)]
        string $visibility
    ): FileInterface;

    /**
     * Copies file to similar or another filesystem.
     *
     * @param string $source source pathname to copy
     * @param string $destination destination pathname
     * @param BucketInterface|null $storage destination storage
     * @param array $config specific config based on used adapter
     * @throws FileOperationException
     */
    public function copy(
        string $source,
        string $destination,
        BucketInterface $storage = null,
        array $config = []
    ): FileInterface;

    /**
     * Moves to similar or another filesystem.
     *
     * @param string $source source pathname to copy
     * @param string $destination destination pathname
     * @param BucketInterface|null $storage destination storage
     * @param array $config specific config based on used adapter
     * @throws FileOperationException
     */
    public function move(
        string $source,
        string $destination,
        BucketInterface $storage = null,
        array $config = []
    ): FileInterface;

    /**
     * Deletes file from storage.
     *
     * @param string $pathname relative filepath
     * @param bool $clean delete directory if directory is empty
     * @throws FileOperationException
     */
    public function delete(string $pathname, bool $clean = false): void;
}
