<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerInterface;

interface ContainerizedInterface extends RouteInterface
{
    /**
     * Associated route with given container.
     *
     * @param ContainerInterface $container
     * @return ContainerizedInterface|$this
     */
    public function withContainer(ContainerInterface $container): ContainerizedInterface;

    /**
     * Indicates that route has associated container.
     *
     * @return bool
     */
    public function hasContainer(): bool;
}
