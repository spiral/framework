<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Builder\Adapter;

use League\Flysystem\FilesystemAdapter;
use Spiral\Storage\Config\DTO\FileSystemInfo\Aws\AwsS3Info;
use Spiral\Storage\Config\DTO\FileSystemInfo\FileSystemInfoInterface;

/**
 * @property FileSystemInfoInterface|AwsS3Info $fsInfo
 */
class AwsS3Builder extends AbstractBuilder
{
    protected const FILE_SYSTEM_INFO_CLASS = AwsS3Info::class;

    /**
     * @inheritDoc
     */
    public function buildSimple(): FilesystemAdapter
    {
        $adapterClass = $this->fsInfo->getAdapterClass();

        return new $adapterClass(
            $this->fsInfo->getClient(),
            $this->fsInfo->getOption(AwsS3Info::BUCKET_KEY)
        );
    }

    /**
     * @inheritDoc
     */
    public function buildAdvanced(): FilesystemAdapter
    {
        $adapterClass = $this->fsInfo->getAdapterClass();

        return new $adapterClass(
            $this->fsInfo->getClient(),
            $this->fsInfo->getOption(AwsS3Info::BUCKET_KEY),
            $this->fsInfo->hasOption(AwsS3Info::PATH_PREFIX_KEY)
                ? $this->fsInfo->getOption(AwsS3Info::PATH_PREFIX_KEY)
                : '',
            $this->fsInfo->getVisibilityConverter()
        );
    }
}
