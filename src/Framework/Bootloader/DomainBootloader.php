<?php

declare(strict_types=1);

namespace Spiral\Bootloader;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Core;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\InterceptorPipeline;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\InterceptorInterface;

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
    ): CoreInterface&HandlerInterface {
        $pipeline = (new InterceptorPipeline($dispatcher))->withCore($core);

        foreach (static::defineInterceptors() as $interceptor) {
            if (!$interceptor instanceof CoreInterceptorInterface && !$interceptor instanceof InterceptorInterface) {
                $interceptor = $container->get($interceptor);
            }

            $pipeline->addInterceptor($interceptor);
        }

        return $pipeline;
    }

    /**
     * Defines list of interceptors.
     */
    protected static function defineInterceptors(): array
    {
        return static::INTERCEPTORS;
    }
}
