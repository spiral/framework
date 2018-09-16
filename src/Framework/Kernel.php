<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloaders\CoreBootloader;
use Spiral\Boot\Exceptions\FrameworkException;
use Spiral\Debug\Bootloaders\DebugBootloader;
use Spiral\Encrypter\Bootloaders\EncrypterBootloader;
use Spiral\Tokenizer\Bootloaders\TokenizerBootloader;

abstract class Kernel extends AbstractKernel
{
    const SYSTEM = [
        CoreBootloader::class,
        DebugBootloader::class,
        EncrypterBootloader::class,
        TokenizerBootloader::class,
    ];

    /**
     * Normalizes directory list and adds all required alises.
     *
     * @param array $directories
     * @return array
     */
    protected function mapDirectories(array $directories): array
    {
        if (!isset($directories['root'])) {
            throw new FrameworkException("Missing required directory `root`.");
        }

        if (!isset($directories['app'])) {
            $directories['app'] = $directories['root'] . '/app/';
        }

        return array_merge([
            // public root
            'public'    => $directories['root'] . '/public/',

            // data directories
            'runtime'   => $directories['root'] . '/runtime/',
            'cache'     => $directories['root'] . '/runtime/cache/',

            // application directories
            'config'    => $directories['app'] . '/config/',
            'resources' => $directories['app'] . '/resources/',
        ], $directories);
    }
}