<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Http\Exception\InvalidRequestScopeException;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Core\InvokerInterface;
use Spiral\Framework\Spiral;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\CurrentRequest;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Spiral\Telemetry\Bootloader\TelemetryBootloader;
use Spiral\Telemetry\TracerFactoryInterface;

/**
 * Configures Http dispatcher.
 */
#[Singleton]
final class HttpBootloader extends Bootloader
{
    public function __construct(
        private readonly ConfiguratorInterface $config,
        private readonly BinderInterface $binder,
    ) {
    }

    public function defineDependencies(): array
    {
        return [
            TelemetryBootloader::class,
        ];
    }

    public function defineSingletons(): array
    {
        $httpBinder = $this->binder->getBinder(Spiral::Http);

        $httpBinder->bindSingleton(Http::class, [self::class, 'httpCore']);
        $httpBinder->bindSingleton(CurrentRequest::class, CurrentRequest::class);
        $httpBinder->bind(
            RequestInterface::class,
            new \Spiral\Core\Config\Proxy(
                interface: RequestInterface::class,
                fallbackFactory: static fn (ContainerInterface $c): RequestInterface => $c
                    ->get(CurrentRequest::class)
                    ->get() ?? throw new InvalidRequestScopeException(
                        RequestInterface::class,
                        $c instanceof Container ? $c : null,
                    ),
            ),
        );

        /**
         * @deprecated since v3.12. Will be removed in v4.0.
         */
        $this->binder->bindSingleton(
            Http::class,
            function (InvokerInterface $invoker, #[Proxy] ContainerInterface $container): Http {
                @trigger_error(\sprintf(
                    'Using `%s` outside of the `%s` scope is deprecated and will be impossible in version 4.0.',
                    Http::class,
                    Spiral::Http->value
                ), \E_USER_DEPRECATED);

                return $invoker->invoke([self::class, 'httpCore'], ['container' => $container]);
            }
        );

        return [];
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

    /**
     * @deprecated since v3.12. Will be removed in v4.0 and replaced with callback.
     */
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
