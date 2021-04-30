<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Config\DTO;

use Spiral\Storage\Config\DTO\FileSystemInfo\FileSystemInfoInterface;
use Spiral\Storage\Config\DTO\Traits\OptionsTrait;

class BucketInfo implements BucketInfoInterface
{
    use OptionsTrait;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $server;

    /**
     * @var FileSystemInfoInterface|null
     */
    protected $fileSystemInfo = null;

    /**
     * @param string $name
     * @param string $server
     * @param array $info
     */
    public function __construct(string $name, string $server, array $info = [])
    {
        $this->name = $name;
        $this->server = $server;

        if (array_key_exists(static::OPTIONS_KEY, $info)) {
            $this->options = $info[static::OPTIONS_KEY];
        }
    }

    /**
     * @inheritDoc
     */
    public function getServer(): string
    {
        return $this->server;
    }

    /**
     * @inheritDoc
     */
    public function setFileSystemInfo(FileSystemInfoInterface $fileSystemInfo): BucketInfoInterface
    {
        $this->fileSystemInfo = $fileSystemInfo;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFileSystemInfo(): ?FileSystemInfoInterface
    {
        return $this->fileSystemInfo;
    }
}
