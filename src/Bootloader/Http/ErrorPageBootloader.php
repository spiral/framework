<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Http;

use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Core\Container\Autowire;
use Spiral\Http\Middleware\ExceptionWrapper;

/**
 * Enable exception wrapping within HTTP requests.
 */
class ErrorPageBootloader extends Bootloader
{
    const BOOT = true;

    /**
     * @param ConfiguratorInterface $configurator
     * @param EnvironmentInterface  $environment
     */
    public function boot(ConfiguratorInterface $configurator, EnvironmentInterface $environment)
    {
        $configurator->modify(
            'http',
            new AppendPatch('middleware', null, new Autowire(
                ExceptionWrapper::class,
                ['suppressErrors' => !$environment->get('DEBUG', false)]
            ))
        );
    }
}