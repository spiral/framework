<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use League\Flysystem\FilesystemOperator;
use Spiral\Storage\Exception\MountException;

interface StorageInterface extends StorageReaderInterface, StorageWriterInterface
{
    /**
     * Get concrete filesystem for specific operations
     *
     * @param string $key
     *
     * @return FilesystemOperator
     *
     * @throws MountException
     */
    public function getFileSystem(string $key): FilesystemOperator;

    /**
     * Get list of all mounted filesystems
     *
     * @return array
     */
    public function getFileSystemsNames(): array;
}
