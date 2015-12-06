<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

/**
 * Provides ability to publish module files such as configs, images and etc.
 */
interface PublisherInterface
{
    const OVERWRITE = true;
    const FOLLOW    = false;

    public function publishDirectory($source, $destination, $merge = self::FOLLOW);
}