<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

use Spiral\Broadcasting\Config\BroadcastConfig;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;

final class BroadcastManager implements BroadcastManagerInterface, SingletonInterface
{
    private FactoryInterface $factory;
    private BroadcastConfig $config;
    /** @var BroadcastInterface[] */
    private array $connections = [];

    public function __construct(
        FactoryInterface $factory,
        BroadcastConfig $config
    ) {
        $this->factory = $factory;
        $this->config = $config;
    }

    public function connection(?string $name = null): BroadcastInterface
    {
        $name = $name ?: $this->config->getDefaultConnection();

        // Replaces alias with real storage name
        $name = $this->config->getAliases()[$name] ?? $name;

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        return $this->connections[$name] = $this->resolve($name);
    }

    private function resolve(string $name): BroadcastInterface
    {
        $config = $this->config->getConnectionConfig($name);

        return $this->factory->make($config['driver'], $config);
    }
}
