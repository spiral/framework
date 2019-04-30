<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Http;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Http\Middleware\CsrfMiddleware;

/**
 * Enable CSRF protection.
 */
class CsrfBootloader extends Bootloader
{
    const BOOT = true;

    /**
     * @param ConfiguratorInterface $configurator
     */
    public function boot(ConfiguratorInterface $configurator)
    {
        $configurator->modify(
            'http',
            new AppendPatch('middleware', null, CsrfMiddleware::class)
        );
    }
}