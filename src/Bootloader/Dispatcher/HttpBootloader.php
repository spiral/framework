<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Dispatcher;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Filters\InputInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\ErrorHandler\NullRenderer;
use Spiral\Http\ErrorHandler\RendererInterface;
use Spiral\Http\HttpCore;
use Spiral\Http\HttpDispatcher;
use Spiral\Http\Pipeline;
use Spiral\Http\RequestInput;
use Spiral\Http\ResponseFactory;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class HttpBootloader extends Bootloader implements SingletonInterface
{
    const BOOT = true;

    const SINGLETONS = [
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
     * @param KernelInterface       $kernel
     * @param HttpDispatcher        $http
     * @param ConfiguratorInterface $configurator
     */
    public function boot(
        KernelInterface $kernel,
        HttpDispatcher $http,
        ConfiguratorInterface $configurator
    ) {
        $kernel->addDispatcher($http);

        $configurator->setDefaults('http', [
            'basePath'   => '/',
            'headers'    => [
                'Content-Type' => 'text/html; charset=UTF-8'
            ],
            'middleware' => [],
            'cookies'    => [
                'domain'   => '.%s',
                'method'   => HttpConfig::COOKIE_ENCRYPT,
                'excluded' => ['csrf-token']
            ],
            'csrf'       => [
                'cookie'   => 'csrf-token',
                'length'   => 16,
                'lifetime' => 86400
            ]
        ]);
    }

    /**
     * @param HttpConfig               $config
     * @param Pipeline                 $pipeline
     * @param RequestHandlerInterface  $handler
     * @param ResponseFactoryInterface $responseFactory
     * @param ContainerInterface       $container
     * @return HttpCore
     */
    protected function core(
        HttpConfig $config,
        Pipeline $pipeline,
        RequestHandlerInterface $handler,
        ResponseFactoryInterface $responseFactory,
        ContainerInterface $container
    ): HttpCore {
        $core = new HttpCore($config, $pipeline, $responseFactory, $container);
        $core->setHandler($handler);

        return $core;
    }
}