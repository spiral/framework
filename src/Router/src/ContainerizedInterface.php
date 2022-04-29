<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerInterface;

interface ContainerizedInterface extends RouteInterface
{
    /**
     * Associated route with given container.
     */
    public function withContainer(ContainerInterface $container): ContainerizedInterface;

    /**
     * Indicates that route has associated container.
     */
    public function hasContainer(): bool;
}
