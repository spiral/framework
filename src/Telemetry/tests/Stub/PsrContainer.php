<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry\Stub;

use Psr\Container\ContainerInterface;

/**
 * A container other than {@see \Spiral\Core\Container}, delegating to one.
 *
 * @internal
 */
final class PsrContainer implements ContainerInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    public function get(string $id): mixed
    {
        return $this->container->get($id);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}
