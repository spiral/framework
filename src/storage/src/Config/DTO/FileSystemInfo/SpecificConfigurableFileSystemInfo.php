<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Config\DTO\FileSystemInfo;

use Spiral\Storage\Exception\StorageException;

interface SpecificConfigurableFileSystemInfo
{
    /**
     * Construct specific DTO parts by info
     *
     * @param array $info
     *
     * @throws StorageException
     */
    public function constructSpecific(array $info): void;
}
