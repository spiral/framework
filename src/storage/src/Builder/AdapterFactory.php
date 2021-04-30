<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Builder;

use League\Flysystem\FilesystemAdapter;
use Spiral\Storage\Builder\Adapter as AdapterBuilder;
use Spiral\Storage\Config\DTO\FileSystemInfo;
use Spiral\Storage\Exception\StorageException;

class AdapterFactory
{
    /**
     * Build filesystem adapter by provided filesystem info
     *
     * @param FileSystemInfo\FileSystemInfoInterface $info
     *
     * @return FilesystemAdapter
     *
     * @throws StorageException
     */
    public static function build(FileSystemInfo\FileSystemInfoInterface $info): FilesystemAdapter
    {
        $builder = static::detectAdapterBuilder($info);

        if ($info->isAdvancedUsage()) {
            return $builder->buildAdvanced();
        }

        return $builder->buildSimple();
    }

    /**
     * Detect required builder by filesystem info
     *
     * @param FileSystemInfo\FileSystemInfoInterface $info
     *
     * @return AdapterBuilder\AdapterBuilderInterface
     *
     * @throws StorageException
     */
    private static function detectAdapterBuilder(
        FileSystemInfo\FileSystemInfoInterface $info
    ): AdapterBuilder\AdapterBuilderInterface {
        switch (get_class($info)) {
            case FileSystemInfo\LocalInfo::class:
                return new AdapterBuilder\LocalBuilder($info);
            case FileSystemInfo\Aws\AwsS3Info::class:
                return new AdapterBuilder\AwsS3Builder($info);
            default:
                throw new StorageException(
                    \sprintf('Adapter can\'t be built by filesystem info `%s`', $info->getName())
                );
        }
    }
}
