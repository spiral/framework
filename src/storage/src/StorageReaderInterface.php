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

interface StorageReaderInterface
{
    /**
     * Check if file exists
     *
     * @param string $uri
     *
     * @return bool
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function fileExists(string $uri): bool;

    /**
     * Read file and return its content as string
     *
     * @param string $uri
     *
     * @return string
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function read(string $uri): string;

    /**
     * Read file and return its content as stream resource
     *
     * @param string $uri
     *
     * @return resource
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function readStream(string $uri);

    /**
     * Get the timestamp of last file modification
     *
     * @param string $uri
     *
     * @return int
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function lastModified(string $uri): int;

    /**
     * Get the file size in bytes
     *
     * @param string $uri
     *
     * @return int
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function fileSize(string $uri): int;

    /**
     * Get the file mime type
     *
     * @param string $uri
     *
     * @return string
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function mimeType(string $uri): string;

    /**
     * Get the file visibility as public/private
     *
     * @param string $uri
     *
     * @return string
     *
     * @throws FileOperationException
     * @throws MountException
     * @throws UriException
     */
    public function visibility(string $uri): string;
}
