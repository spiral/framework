<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Modules;

use Spiral\Files\FilesInterface;
use Spiral\Modules\Exception\PublishException;

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
     * @param string $filename
     * @param string $destination
     * @param bool   $merge
     * @param int    $mode
     *
     * @throws PublishException
     */
    public function publish(
        string $filename,
        string $destination,
        bool $merge = self::FOLLOW,
        int $mode = FilesInterface::READONLY
    );

    /**
     * Publish content of specified directory.
     *
     * @param string $directory
     * @param string $destination
     * @param bool   $merge
     * @param int    $mode
     *
     * @throws PublishException
     */
    public function publishDirectory(
        string $directory,
        string $destination,
        bool $merge = self::REPLACE,
        int $mode = FilesInterface::READONLY
    );

    /**
     * Ensure that specified directory exists and has valid file permissions.
     *
     * @param string $directory
     * @param int    $mode
     *
     * @throws PublishException
     */
    public function ensureDirectory(string $directory, int $mode = FilesInterface::READONLY);
}