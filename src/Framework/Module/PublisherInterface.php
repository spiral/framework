<?php

declare(strict_types=1);

namespace Spiral\Module;

use Spiral\Files\FilesInterface;
use Spiral\Module\Exception\PublishException;

/**
 * Provides ability to publish module files such as configs, images and etc.
 */
interface PublisherInterface
{
    // Merge rules
    public const REPLACE = 'replace';
    public const FOLLOW  = 'follow';

    /**
     * Publish single file.
     *
     * @throws PublishException
     */
    public function publish(
        string $filename,
        string $destination,
        string $mergeMode = self::FOLLOW,
        int $mode = FilesInterface::READONLY
    ): void;

    /**
     * Publish content of specified directory.
     *
     * @throws PublishException
     */
    public function publishDirectory(
        string $directory,
        string $destination,
        string $mergeMode = self::REPLACE,
        int $mode = FilesInterface::READONLY
    ): void;

    /**
     * Ensure that specified directory exists and has valid file permissions.
     *
     * @throws PublishException
     */
    public function ensureDirectory(string $directory, int $mode = FilesInterface::READONLY): void;
}
