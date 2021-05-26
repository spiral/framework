<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Storage;

use JetBrains\PhpStorm\ExpectedValues;
use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\FileInterface;
use Spiral\Storage\StorageInterface;
use Spiral\Storage\Visibility;

/**
 * @psalm-import-type VisibilityType from Visibility
 */
interface WritableInterface
{
    /**
     * Creates new empty file.
     *
     * @param string $pathname
     * @param array $config
     * @return FileInterface
     * @throws FileOperationException
     */
    public function create(string $pathname, array $config = []): FileInterface;

    /**
     * Write provided content to defined filesystem as file by defined filepath.
     *
     * @param string $pathname relative filepath
     * @param string|\Stringable|resource $content content string or stream to write
     * @param array $config specific config based on used adapter
     * @return FileInterface
     * @throws FileOperationException
     */
    public function write(string $pathname, $content, array $config = []): FileInterface;

    /**
     * Sets file visibility.
     *
     * @param string $pathname
     * @param VisibilityType $visibility
     * @return FileInterface
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
     * @param StorageInterface|null $storage destination storage
     * @param array $config specific config based on used adapter
     * @return FileInterface
     * @throws FileOperationException
     */
    public function copy(
        string $source,
        string $destination,
        StorageInterface $storage = null,
        array $config = []
    ): FileInterface;

    /**
     * Moves to similar or another filesystem.
     *
     * @param string $source source pathname to copy
     * @param string $destination destination pathname
     * @param StorageInterface|null $storage destination storage
     * @param array $config specific config based on used adapter
     * @return FileInterface
     * @throws FileOperationException
     */
    public function move(
        string $source,
        string $destination,
        StorageInterface $storage = null,
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
