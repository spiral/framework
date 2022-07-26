<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Auth;

use Spiral\Auth\Config\AuthConfig;
use Spiral\Auth\HttpTransportInterface;
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
use Spiral\Http\Config\HttpConfig;

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

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(): void
    {
        $this->config->setDefaults(
            AuthConfig::CONFIG,
            [
                'defaultTransport' => 'cookie',
                'transports' => [],
            ]
        );
    }

    public function boot(): void
    {
        $this->addTransport('cookie', $this->createDefaultCookieTransport());
        $this->addTransport('header', new HeaderTransport('X-Auth-Token'));
    }

    /**
     * Add new Http token transport.
     */
    public function addTransport(string $name, Autowire|HttpTransportInterface|string $transport): void
    {
        $this->config->modify(AuthConfig::CONFIG, new Append('transports', $name, $transport));
    }

    /**
     * Creates default cookie transport when "transports" section is empty.
     */
    private function createDefaultCookieTransport(): CookieTransport
    {
        $config = $this->config->getConfig(HttpConfig::CONFIG);

        return new CookieTransport('token', $config['basePath'] ?? '/');
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
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
