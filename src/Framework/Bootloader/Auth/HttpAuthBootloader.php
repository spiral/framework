<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Auth;

use Spiral\Auth\Config\AuthConfig;
use Spiral\Auth\HttpTransportInterface;
use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Auth\Transport\CookieTransport;
use Spiral\Auth\Transport\HeaderTransport;
use Spiral\Auth\TransportRegistry;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;

/**
 * Enables Auth middleware and http transports to read and write tokens in PSR-7 request/response.
 */
final class HttpAuthBootloader extends Bootloader implements SingletonInterface
{
    protected const DEPENDENCIES = [
        AuthBootloader::class,
        HttpBootloader::class,
    ];

    protected const SINGLETONS = [
        TransportRegistry::class => [self::class, 'transportRegistry'],
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
     * @param HttpBootloader $http
     */
    public function boot(HttpBootloader $http): void
    {
        $http->addMiddleware(AuthMiddleware::class);

        $this->config->setDefaults(
            'auth',
            [
                'defaultTransport' => 'cookie',
                'transports'       => [
                    'cookie' => $this->createDefaultCookieTransport(),
                    'header' => new HeaderTransport('X-Auth-Token'),
                ],
            ]
        );
    }

    /**
     * Add new Http token transport.
     *
     * @param string                                 $name
     * @param HttpTransportInterface|Autowire|string $transport
     */
    public function addTransport(string $name, $transport): void
    {
        $this->config->modify('auth', new Append('transports', $name, $transport));
    }

    /**
     * Creates default cookie transport when "transports" section is empty.
     *
     * @return CookieTransport
     */
    private function createDefaultCookieTransport(): CookieTransport
    {
        $config = $this->config->getConfig('http');

        return new CookieTransport('token', $config['basePath'] ?? '/');
    }

    /**
     * @param AuthConfig       $config
     * @param FactoryInterface $factory
     * @return TransportRegistry
     */
    private function transportRegistry(AuthConfig $config, FactoryInterface $factory): TransportRegistry
    {
        $registry = new TransportRegistry();
        $registry->setDefaultTransport($config->getDefaultTransport());

        foreach ($config->getTransports() as $name => $transport) {
            if ($transport instanceof Autowire) {
                $transport = $transport->resolve($factory);
            }

            $registry->setTransport($name, $transport);
        }

        return $registry;
    }
}
