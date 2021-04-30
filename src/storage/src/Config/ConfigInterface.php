<?php

/**
 * This file is part of storage-engine package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Config;

use Spiral\Storage\Config\DTO\BucketInfoInterface;
use Spiral\Storage\Config\DTO\FileSystemInfo\FileSystemInfoInterface;
use Spiral\Storage\Exception\StorageException;

interface ConfigInterface
{
    /**
     * Get all defined servers keys.
     *
     * @return string[]
     */
    public function getServersKeys(): array;

    /**
     * Check if server was defined.
     *
     * @param string $key
     * @return bool
     */
    public function hasServer(string $key): bool;

    /**
     * Get all defined buckets keys.
     *
     * @return string[]
     */
    public function getBucketsKeys(): array;

    /**
     * Check if bucket was defined.
     *
     * @param string $key
     * @return bool
     */
    public function hasBucket(string $key): bool;

    /**
     * Get defined temp directory.
     * System temp directory by default.
     *
     * @return string
     */
    public function getTmpDir(): string;

    /**
     * Build filesystem info by provided fs (bucket) label.
     * Force mode allows to rebuild fs info for internal filesystems info list.
     *
     * @param string $fs
     * @param bool|null $force
     * @return FileSystemInfoInterface
     * @throws StorageException
     */
    public function buildFileSystemInfo(string $fs, ?bool $force = false): FileSystemInfoInterface;

    /**
     * Build bucket info by provided label.
     * Force mode allows to rebuild bucket info for internal list.
     *
     * @param string $bucketLabel
     * @param bool|null $force
     * @return BucketInfoInterface
     * @throws StorageException
     */
    public function buildBucketInfo(string $bucketLabel, ?bool $force = false): BucketInfoInterface;
}

