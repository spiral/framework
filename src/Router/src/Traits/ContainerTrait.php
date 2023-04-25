<?php

declare(strict_types=1);

namespace Spiral\Router\Traits;

use Psr\Container\ContainerInterface;

trait ContainerTrait
{
    protected ?ContainerInterface $container = null;

    /**
     * Indicates that route has associated container.
     *
     * @psalm-assert-if-true ContainerInterface $this->container
     * @psalm-assert-if-false null $this->container
     */
    public function hasContainer(): bool
    {
        return $this->container !== null;
    }
}
