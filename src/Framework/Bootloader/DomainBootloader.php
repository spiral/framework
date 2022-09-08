<?php

declare(strict_types=1);

namespace Spiral\Bootloader;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Core;
use Spiral\Core\InterceptableCore;

/**
 * Configures global domain core (CoreInterface) with the set of interceptors to alter domain layer functionality.
 *
 * The CoreInterface binding must be complete in child implementation.
 */
abstract class DomainBootloader extends Bootloader
{
    // the set of interceptors for the domain code
    protected const INTERCEPTORS = [];

    protected static function domainCore(
        Core $core,
        ContainerInterface $container,
        ?EventDispatcherInterface $dispatcher = null
    ): InterceptableCore {
        $interceptableCore = new InterceptableCore($core, $dispatcher);

        foreach (static::INTERCEPTORS as $interceptor) {
            $interceptableCore->addInterceptor($container->get($interceptor));
        }

        return $interceptableCore;
    }
}
