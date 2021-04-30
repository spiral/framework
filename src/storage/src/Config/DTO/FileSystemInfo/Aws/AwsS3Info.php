<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Config\DTO\FileSystemInfo\Aws;

use Spiral\Storage\Config\DTO\FileSystemInfo\FileSystemInfo;
use Spiral\Storage\Config\DTO\FileSystemInfo\SpecificConfigurableFileSystemInfo;
use Spiral\Storage\Resolver\AwsS3Resolver;

class AwsS3Info extends FileSystemInfo implements SpecificConfigurableFileSystemInfo
{
    public const BUCKET_KEY = 'bucket';
    public const CLIENT_KEY = 'client';
    public const PATH_PREFIX_KEY = 'path-prefix';

    protected const FILE_SYSTEM_INFO_TYPE = 'awsS3';

    protected const REQUIRED_OPTIONS = [
        self::BUCKET_KEY => self::STRING_TYPE,
        self::CLIENT_KEY => self::MIXED_TYPE,
    ];

    protected const ADDITIONAL_OPTIONS = [
        self::PATH_PREFIX_KEY => self::STRING_TYPE,
        self::VISIBILITY_KEY => self::ARRAY_TYPE,
    ];

    /**
     * @var string
     */
    protected $resolver = AwsS3Resolver::class;

    /**
     * @var AwsVisibilityConverter|null
     */
    protected $visibilityConverter = null;

    /**
     * @inheritDoc
     */
    public function constructSpecific(array $info): void
    {
        if ($this->hasOption(static::VISIBILITY_KEY)) {
            $this->visibilityConverter = new AwsVisibilityConverter($this->getOption(static::VISIBILITY_KEY));
        }
    }

    /**
     * Get prepared visibility converter
     *
     * @return mixed|null
     */
    public function getVisibilityConverter()
    {
        return $this->visibilityConverter instanceof AwsVisibilityConverter
            ? $this->visibilityConverter->getConverter()
            : null;
    }

    /**
     * Get S3 client
     *
     * @return mixed|null
     */
    public function getClient()
    {
        return $this->getOption(static::CLIENT_KEY);
    }
}
