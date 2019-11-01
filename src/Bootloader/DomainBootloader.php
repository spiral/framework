<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Core;
use Spiral\Core\CoreInterface;
use Spiral\Core\InterceptableCore;

/**
 * Configures global domain core (CoreInterface) with the set of interceptors to alter domain layer functionality.
 */
abstract class DomainBootloader extends Bootloader
{
    protected const SINGLETONS = [
        CoreInterface::class => [self::class, 'domainCore']
    ];

    // the set of interceptors for the domain code
    protected const INTERCEPTORS = [];

    /**
     * @param Core               $core
     * @param ContainerInterface $container
     * @return InterceptableCore
     */
    protected function domainCore(Core $core, ContainerInterface $container)
    {
        $core = new InterceptableCore($core);

        foreach (static::INTERCEPTORS as $interceptor) {
            $core->addInterceptor($container->get($interceptor));
        }

        return $core;
    }
}
