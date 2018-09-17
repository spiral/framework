<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Http\Bootloaders;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Core;
use Spiral\Core\CoreInterface;
use Spiral\Filters\InputInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\HttpCore;
use Spiral\Http\HttpDispatcher;
use Spiral\Http\Pipeline;
use Spiral\Http\RequestInput;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class HttpBootloader extends Bootloader implements SingletonInterface
{
    const BOOT = true;

    const SINGLETONS = [
        EmitterInterface::class         => SapiEmitter::class,
        ResponseFactoryInterface::class => HttpCore::class,
        CoreInterface::class            => Core::class,
        HttpCore::class                 => [self::class, 'core'],
        RouterInterface::class          => [self::class, 'router'],
        InputInterface::class           => RequestInput::class
    ];

    /**
     * @param KernelInterface $kernel
     * @param HttpDispatcher  $http
     */
    public function boot(KernelInterface $kernel, HttpDispatcher $http)
    {
        $kernel->addDispatcher($http);
    }

    /**
     * @param HttpConfig         $config
     * @param ContainerInterface $container
     * @param RouterInterface    $router
     * @param Pipeline           $pipeline
     * @return HttpCore
     */
    protected function core(
        HttpConfig $config,
        ContainerInterface $container,
        RouterInterface $router,
        Pipeline $pipeline
    ): HttpCore {
        $core = new HttpCore($config, $pipeline, $container);
        $core->setHandler($router);

        return $core;
    }

    /**
     * @param HttpConfig         $config
     * @param ContainerInterface $container
     * @return RouterInterface
     */
    protected function router(
        HttpConfig $config,
        ContainerInterface $container
    ): RouterInterface {
        return new Router($config->basePath(), $container);
    }
}