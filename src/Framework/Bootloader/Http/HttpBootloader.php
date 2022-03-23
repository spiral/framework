<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Emitter\SapiEmitter;
use Spiral\Http\EmitterInterface;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Spiral\Http\SapiDispatcher;

/**
 * Configures Http dispatcher in SAPI and RoadRunner modes (if available).
 */
final class HttpBootloader extends Bootloader implements SingletonInterface
{
    protected const SINGLETONS = [
        Http::class             => [self::class, 'httpCore'],
        EmitterInterface::class => SapiEmitter::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function boot(AbstractKernel $kernel, FactoryInterface $factory): void
    {
        $this->config->setDefaults(
            HttpConfig::CONFIG,
            [
                'basePath'   => '/',
                'headers'    => [
                    'Content-Type' => 'text/html; charset=UTF-8',
                ],
                'middleware' => [],
            ]
        );

        // Lowest priority
        $kernel->started(static function (AbstractKernel $kernel) use ($factory): void {
            $kernel->addDispatcher($factory->make(SapiDispatcher::class));
        });
    }

    /**
     * Register new http middleware.
     *
     * @psalm-param MiddlewareInterface|class-string<MiddlewareInterface>
     */
    public function addMiddleware(string|MiddlewareInterface $middleware): void
    {
        $this->config->modify('http', new Append('middleware', null, $middleware));
    }

    protected function httpCore(
        HttpConfig $config,
        Pipeline $pipeline,
        RequestHandlerInterface $handler,
        ResponseFactoryInterface $responseFactory,
        ContainerInterface $container
    ): Http {
        $core = new Http($config, $pipeline, $responseFactory, $container);
        $core->setHandler($handler);

        return $core;
    }
}
