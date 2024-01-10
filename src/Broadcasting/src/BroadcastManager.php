<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

use Spiral\Broadcasting\Config\BroadcastConfig;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\FactoryInterface;

#[Singleton]
final class BroadcastManager implements BroadcastManagerInterface
{
    /** @var BroadcastInterface[] */
    private array $connections = [];

    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly BroadcastConfig $config
    ) {
    }

    public function connection(?string $name = null): BroadcastInterface
    {
        $name ??= $this->config->getDefaultConnection();

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
