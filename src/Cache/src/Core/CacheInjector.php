<?php

declare(strict_types=1);

namespace Spiral\Cache\Core;

use ReflectionClass;
use Spiral\Cache\CacheRepository;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Cache\Exception\InvalidArgumentException;
use Spiral\Cache\CacheStorageProviderInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @implements InjectorInterface<CacheInterface>
 */
final class CacheInjector implements InjectorInterface
{
    public function __construct(
        private readonly CacheStorageProviderInterface $provider
    ) {
    }

    public function createInjection(ReflectionClass $class, string $context = null): CacheInterface
    {
        try {
            if ($context === null) {
                $connection = $this->provider->storage();
            } else {
                // Get Cache by context
                try {
                    $connection = $this->provider->storage($context);
                } catch (InvalidArgumentException) {
                    // Case when context doesn't match to configured connections
                    return $this->provider->storage();
                }
            }

            $this->matchType($class, $context, $connection);
        } catch (\Throwable $e) {
            throw new ContainerException(sprintf("Can't inject the required cache. %s", $e->getMessage()), 0, $e);
        }

        return $connection;
    }

    /**
     * Check the resolved connection implements required type
     *
     * @throws \RuntimeException
     */
    private function matchType(ReflectionClass $class, ?string $context, CacheInterface $connection): void
    {
        if ($connection::class === CacheRepository::class) {
            $connection = $connection->getStorage();
        }
        $className = $class->getName();
        if ($className !== CacheInterface::class && !$connection instanceof $className) {
            throw new \RuntimeException(
                \sprintf(
                    "The cache obtained by the context `%s` doesn't match the type `%s`.",
                    $context ?? 'NULL',
                    $className
                )
            );
        }
    }
}
