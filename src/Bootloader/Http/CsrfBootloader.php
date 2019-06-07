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
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Csrf\Middleware\CsrfMiddleware;

final class CsrfBootloader extends Bootloader implements DependedInterface
{
    /**
     * @param ConfiguratorInterface $config
     * @param HttpBootloader        $http
     */
    public function boot(ConfiguratorInterface $config, HttpBootloader $http)
    {
        $config->setDefaults('csrf', [
            'cookie'   => 'csrf-token',
            'length'   => 16,
            'lifetime' => 86400
        ]);

        $http->addMiddleware(CsrfMiddleware::class);
    }

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [
            HttpBootloader::class
        ];
    }
}