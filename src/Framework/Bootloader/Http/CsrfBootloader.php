<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Csrf\Middleware\CsrfMiddleware;

final class CsrfBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        HttpBootloader::class,
    ];

    /**
     * @param ConfiguratorInterface $config
     * @param HttpBootloader        $http
     */
    public function boot(ConfiguratorInterface $config, HttpBootloader $http): void
    {
        $config->setDefaults(
            'csrf',
            [
                'cookie'   => 'csrf-token',
                'length'   => 16,
                'lifetime' => 86400,
                'secure'   => true,
                'sameSite' => null,
            ]
        );

        $http->addMiddleware(CsrfMiddleware::class);
    }
}
