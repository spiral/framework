<?php

declare(strict_types=1);

namespace Spiral\Auth;

use Spiral\Auth\Config\AuthConfig;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;

class TokenStorageProvider implements TokenStorageProviderInterface
{
    /** @var TokenStorageInterface[] */
    private array $storages = [];

    public function __construct(
        private readonly AuthConfig $config,
        private readonly FactoryInterface $factory
    ) {
    }

    public function getStorage(?string $name = null): TokenStorageInterface
    {
        $name ??= $this->config->getDefaultStorage();

        if (isset($this->storages[$name])) {
            return $this->storages[$name];
        }

        return $this->storages[$name] = $this->resolve($name);
    }

    private function resolve(string $name): TokenStorageInterface
    {
        $storage = $this->config->getStorage($name);

        if ($storage instanceof TokenStorageInterface) {
            return $storage;
        }

        if ($storage instanceof Autowire) {
            return $storage->resolve($this->factory);
        }

        return $this->factory->make($storage);
    }
}
