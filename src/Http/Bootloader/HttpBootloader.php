<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Http\Bootloader;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Core;
use Spiral\Core\CoreInterface;
use Spiral\Filters\InputInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Error\RendererInterface;
use Spiral\Http\HttpCore;
use Spiral\Http\HttpDispatcher;
use Spiral\Http\Pipeline;
use Spiral\Http\Error\NullRenderer;
use Spiral\Http\RequestInput;
use Spiral\Http\ResponseFactory;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class HttpBootloader extends Bootloader implements SingletonInterface
{
    const BOOT = true;

    const SINGLETONS = [
        // HMVC Core and routing
        CoreInterface::class            => Core::class,
        RouterInterface::class          => [self::class, 'router'],

        // Error Pages
        RendererInterface::class        => NullRenderer::class,

        // PSR-7 handlers and factories
        EmitterInterface::class         => SapiEmitter::class,
        ResponseFactoryInterface::class => ResponseFactory::class,
        HttpCore::class                 => [self::class, 'core'],

        // Filter input mapper
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
     * @param RouterInterface          $router
     * @param HttpConfig               $config
     * @param Pipeline                 $pipeline
     * @param ResponseFactoryInterface $responseFactory
     * @param ContainerInterface       $container
     * @return HttpCore
     */
    protected function core(
        RouterInterface $router,
        HttpConfig $config,
        Pipeline $pipeline,
        ResponseFactoryInterface $responseFactory,
        ContainerInterface $container
    ): HttpCore {
        $core = new HttpCore($config, $pipeline, $responseFactory, $container);
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