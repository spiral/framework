<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use Spiral\Framework\Exceptions\FrameworkException;

class DirectorySchema
{
    /**
     * @param array $directories
     * @return array
     *
     * @throws FrameworkException
     */
    public static function default(array $directories): array
    {
        if (!isset($directories['root'])) {
            throw new FrameworkException("Missing required directory `root`.");
        }

        if (!isset($directories['app'])) {
            $directories['app'] = $directories['root'] . '/app/';
        }

        return array_merge($directories, [
            // public root
            'public'    => $directories['root'] . '/public/',

            // application directories
            'config'    => $directories['app'] . '/config/',
            'resources' => $directories['app'] . '/resources/',

            // data directories
            'runtime'   => $directories['app'] . '/runtime/',
            'cache'     => $directories['app'] . '/runtime/cache/',
        ]);
    }
}