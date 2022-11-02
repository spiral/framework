<?php

declare(strict_types=1);

namespace Spiral\Auth\Config;

use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\Exception\InvalidArgumentException;
use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;

/**
 * Manages auth http transport configuration.
 */
final class AuthConfig extends InjectableConfig
{
    // Configuration source.
    public const CONFIG = 'auth';

    protected array $config = [
        'defaultTransport' => 'cookie',
        'defaultStorage' => 'session',
        'storages' => [],
        'transports' => [],
    ];

    public function getDefaultTransport(): string
    {
        return $this->config['defaultTransport'];
    }

    /**
     * @return Autowire[]
     */
    public function getTransports(): array
    {
        return \array_map([Autowire::class, 'wire'], $this->config['transports']);
    }

    /**
     * @return TokenStorageInterface|class-string<TokenStorageInterface>|Autowire
     * @throws InvalidArgumentException
     */
    public function getStorage(string $name): TokenStorageInterface|string|Autowire
    {
        if (!isset($this->config['storages'][$name])) {
            throw new InvalidArgumentException(
                \sprintf('Token storage `%s` is not defined.', $name)
            );
        }

        return $this->config['storages'][$name];
    }

    public function getDefaultStorage(): string
    {
        return $this->config['defaultStorage'];
    }
}
