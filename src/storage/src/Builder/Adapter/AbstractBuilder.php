<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Builder\Adapter;

use Spiral\Storage\Config\DTO\FileSystemInfo\FileSystemInfoInterface;
use Spiral\Storage\Exception\StorageException;

abstract class AbstractBuilder implements AdapterBuilderInterface
{
    /**
     * Filesystem info class required for builder
     */
    protected const FILE_SYSTEM_INFO_CLASS = '';

    /**
     * @var FileSystemInfoInterface
     */
    protected $fsInfo;

    /**
     * @param FileSystemInfoInterface $fsInfo
     *
     * @throws StorageException
     */
    public function __construct(FileSystemInfoInterface $fsInfo)
    {
        $requiredClass = static::FILE_SYSTEM_INFO_CLASS;

        if (empty($requiredClass) || !$fsInfo instanceof $requiredClass) {
            throw new StorageException(
                \sprintf('Wrong filesystem info `%s` provided for `%s`', get_class($fsInfo), static::class)
            );
        }

        $this->fsInfo = $fsInfo;
    }
}
