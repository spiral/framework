<?php

declare(strict_types=1);

namespace Spiral\Router\Traits;

use Psr\Container\ContainerInterface;

trait ContainerTrait
{
    protected ?ContainerInterface $container = null;

    /**
     * Indicates that route has associated container.
     */
    public function hasContainer(): bool
    {
        return $this->container !== null;
    }
}
