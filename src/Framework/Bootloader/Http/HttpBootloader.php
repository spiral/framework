<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Spiral\Telemetry\Bootloader\TelemetryBootloader;
use Spiral\Telemetry\TracerFactoryInterface;

/**
 * Configures Http dispatcher.
 */
final class HttpBootloader extends Bootloader implements SingletonInterface
{
    protected const DEPENDENCIES = [
        TelemetryBootloader::class,
    ];

    protected const SINGLETONS = [
        Http::class => [self::class, 'httpCore'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(): void
    {
        $this->config->setDefaults(
            HttpConfig::CONFIG,
            [
                'basePath'   => '/',
                'headers'    => [
                    'Content-Type' => 'text/html; charset=UTF-8',
                ],
                'middleware' => [],
                'chunkSize' => null,
                'inputBags' => [],
            ]
        );
    }

    /**
     * Register new http middleware.
     *
     * @psalm-param MiddlewareInterface|Autowire|class-string<MiddlewareInterface> Middleware definition
     */
    public function addMiddleware(string|Autowire|MiddlewareInterface $middleware): void
    {
        $this->config->modify(HttpConfig::CONFIG, new Append('middleware', null, $middleware));
    }

    /**
     * @param non-empty-string $bag
     * @param array{"class": class-string, "source": string, "alias": string} $config
     */
    public function addInputBag(string $bag, array $config): void
    {
        $this->config->modify(HttpConfig::CONFIG, new Append('inputBags', $bag, $config));
    }

    protected function httpCore(
        HttpConfig $config,
        Pipeline $pipeline,
        RequestHandlerInterface $handler,
        ResponseFactoryInterface $responseFactory,
        ContainerInterface $container,
        TracerFactoryInterface $tracerFactory
    ): Http {
        $core = new Http($config, $pipeline, $responseFactory, $container, $tracerFactory);
        $core->setHandler($handler);

        return $core;
    }
}
