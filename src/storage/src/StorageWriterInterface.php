<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Storage\Exception\FileOperationException;
use Spiral\Storage\Exception\MountException;
use Spiral\Storage\Exception\UriException;

interface StorageWriterInterface
{
    /**
     * Allocate file in defined temp directory and return path to this file
     * In case uri defined its content will be used for new file as content
     *
     * @param string|null $uri
     *
     * @return string
     *
     * @throws FileOperationException
     */
    public function tempFilename(string $uri = null): string;

    /**
     * Write provided content to defined filesystem as file by defined filepath
     *
     * @param string $fileSystem destination filesystem name
     * @param string $filePath relative filepath
     * @param string $content content to write
     * @param array $config specific config based on used adapter
     *
     * @return string uri to the new file
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function write(string $fileSystem, string $filePath, string $content, array $config = []): string;

    /**
     * Write provided content to defined filesystem as file by defined filepath
     *
     * @param string $fileSystem destination filesystem name
     * @param string $filePath relative filepath
     * @param resource $content stream to write
     * @param array $config specific config based on used adapter
     *
     * @return string uri to the new file
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function writeStream(string $fileSystem, string $filePath, $content, array $config = []): string;

    /**
     * Set file visibility
     *
     * @param string $uri
     * @param string $visibility public or private
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function setVisibility(string $uri, string $visibility): void;

    /**
     * Copy file by uri to similar or another filesystem
     *
     * @param string $sourceUri source file to copy
     * @param string $destinationFileSystem destination filesystem name
     * @param string|null $targetFilePath filepath for the new file. Similar to source path on source filesystem
     * @param array $config specific config based on used adapter
     *
     * @return string
     */
    public function copy(
        string $sourceUri,
        string $destinationFileSystem,
        ?string $targetFilePath = null,
        array $config = []
    ): string;

    /**
     * Move file by uri to similar or another filesystem
     *
     * @param string $sourceUri source file to move
     * @param string $destinationFileSystem destination filesystem name
     * @param string|null $targetFilePath filepath for the new file. Similar to source path on source filesystem
     * @param array $config specific config based on used adapter
     *
     * @return string
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function move(
        string $sourceUri,
        string $destinationFileSystem,
        ?string $targetFilePath = null,
        array $config = []
    ): string;

    /**
     * Delete file by uri
     *
     * @param string $uri
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function delete(string $uri): void;
}
