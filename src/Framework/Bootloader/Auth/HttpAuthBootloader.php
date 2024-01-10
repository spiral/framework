<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Auth;

use Spiral\Auth\Config\AuthConfig;
use Spiral\Auth\HttpTransportInterface;
use Spiral\Auth\Session\TokenStorage as SessionTokenStorage;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TokenStorageProvider;
use Spiral\Auth\TokenStorageProviderInterface;
use Spiral\Auth\Transport\CookieTransport;
use Spiral\Auth\Transport\HeaderTransport;
use Spiral\Auth\TransportRegistry;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Http\Config\HttpConfig;

/**
 * Enables Auth middleware and http transports to read and write tokens in PSR-7 request/response.
 */
#[Singleton]
final class HttpAuthBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        AuthBootloader::class,
        HttpBootloader::class,
    ];

    protected const SINGLETONS = [
        TransportRegistry::class => [self::class, 'transportRegistry'],
        TokenStorageInterface::class => [self::class, 'getDefaultTokenStorage'],
        TokenStorageProviderInterface::class => TokenStorageProvider::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(AbstractKernel $kernel, EnvironmentInterface $env): void
    {
        $this->config->setDefaults(
            AuthConfig::CONFIG,
            [
                'defaultTransport' => $env->get('AUTH_TOKEN_TRANSPORT', 'cookie'),
                'defaultStorage' => $env->get('AUTH_TOKEN_STORAGE', 'session'),
                'transports' => [],
                'storages' => [],
            ]
        );

        $kernel->booting(function () {
            $this->addTransport('cookie', $this->createDefaultCookieTransport());
            $this->addTransport('header', new HeaderTransport('X-Auth-Token'));
            $this->addTokenStorage('session', SessionTokenStorage::class);
        });
    }

    /**
     * Add new Http token transport.
     *
     * @param non-empty-string $name
     * @param Autowire|HttpTransportInterface|class-string<HttpTransportInterface> $transport
     */
    public function addTransport(string $name, Autowire|HttpTransportInterface|string $transport): void
    {
        $this->config->modify(AuthConfig::CONFIG, new Append('transports', $name, $transport));
    }

    /**
     * Add new Http token storage.
     *
     * @param non-empty-string $name
     * @param Autowire|TokenStorageInterface|class-string<TokenStorageInterface> $storage
     */
    public function addTokenStorage(string $name, Autowire|TokenStorageInterface|string $storage): void
    {
        $this->config->modify(AuthConfig::CONFIG, new Append('storages', $name, $storage));
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

    /**
     * Get default token storage from provider
     */
    private function getDefaultTokenStorage(TokenStorageProviderInterface $provider): TokenStorageInterface
    {
        return $provider->getStorage();
    }
}
