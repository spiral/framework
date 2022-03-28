<?php

declare(strict_types=1);

namespace Spiral\Router\Traits;

use Spiral\Router\RouteInterface;

trait DefaultsTrait
{
    protected array $defaults = [];

    /**
     * Returns new route instance with forced default values.
     *
     * @return RouteInterface|$this
     */
    public function withDefaults(array $defaults): RouteInterface
    {
        $route = clone $this;
        $route->defaults = $defaults;

        return $route;
    }

    /**
     * Get default route values.
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }
}
