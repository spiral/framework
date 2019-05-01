<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\KernelInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\HttpCore;
use Spiral\Http\Pipeline;
use Spiral\Http\ResponseFactory;
use Spiral\Http\RrDispacher;
use Spiral\Http\SapiDispatcher;
use Spiral\RoadRunner\PSR7Client;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

/**
 * Configures Http dispatcher in SAPI and RoadRunner modes (if available).
 */
final class HttpBootloader extends Bootloader implements SingletonInterface
{
    const SINGLETONS = [
        EmitterInterface::class         => SapiEmitter::class,
        ResponseFactoryInterface::class => ResponseFactory::class,
        HttpCore::class                 => [self::class, 'httpCore'],
    ];

    /** @var ConfiguratorInterface */
    private $config;

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param KernelInterface  $kernel
     * @param FactoryInterface $factory
     */
    public function boot(KernelInterface $kernel, FactoryInterface $factory)
    {
        $kernel->addDispatcher($factory->make(SapiDispatcher::class));

        if (class_exists(PSR7Client::class)) {
            $kernel->addDispatcher($factory->make(RrDispacher::class));
        }

        $this->config->setDefaults('http', [
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
    protected function httpCore(
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

    /**
     * Register new http middleware.
     *
     * @param mixed $middleware
     */
    public function addMiddleware($middleware)
    {
        $this->config->modify('http', new Append('middleware', null, $middleware));
    }

    /**
     * Disable protection for given cookie.
     *
     * @param string $cookie
     */
    public function whitelistCookie(string $cookie)
    {
        $this->config->modify('http', new Append('cookies.excluded', null, $cookie));
    }
}