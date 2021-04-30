<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Config\DTO\FileSystemInfo;

use Spiral\Storage\Resolver\LocalSystemResolver;

class LocalInfo extends FileSystemInfo
{
    public const ROOT_DIR_KEY = 'rootDir';
    public const WRITE_FLAGS_KEY = 'write-flags';
    public const LINK_HANDLING_KEY = 'link-handling';
    public const HOST_KEY = 'host';

    protected const FILE_SYSTEM_INFO_TYPE = 'local';

    protected const REQUIRED_OPTIONS = [
        self::ROOT_DIR_KEY => self::STRING_TYPE,
    ];

    protected const ADDITIONAL_OPTIONS = [
        self::VISIBILITY_KEY => self::ARRAY_TYPE,
        self::WRITE_FLAGS_KEY => self::INT_TYPE,
        self::LINK_HANDLING_KEY => self::INT_TYPE,
        self::HOST_KEY => self::STRING_TYPE,
    ];

    /**
     * @var class-string
     */
    protected $resolver = LocalSystemResolver::class;

    /**
     * @inheritDoc
     */
    public function isAdvancedUsage(): bool
    {
        foreach (static::ADDITIONAL_OPTIONS as $optionalOption => $type) {
            if ($optionalOption === static::HOST_KEY) {
                continue;
            }

            if ($this->hasOption($optionalOption)) {
                return true;
            }
        }

        return false;
    }
}
