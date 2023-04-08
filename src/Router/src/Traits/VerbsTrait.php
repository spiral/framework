<?php

declare(strict_types=1);

namespace Spiral\Router\Traits;

use Spiral\Router\Exception\RouteException;
use Spiral\Router\RouteInterface;

trait VerbsTrait
{
    protected array $verbs = RouteInterface::VERBS;

    /**
     * Attach specific list of HTTP verbs to the route.
     *
     * @return $this
     * @throws RouteException
     */
    public function withVerbs(string ...$verbs): RouteInterface
    {
        foreach ($verbs as &$verb) {
            $verb = \strtoupper($verb);
            if (!\in_array($verb, RouteInterface::VERBS, true)) {
                throw new RouteException(\sprintf('Invalid HTTP verb `%s`', $verb));
            }

            unset($verb);
        }
        unset($verb);

        $route = clone $this;
        $route->verbs = $verbs;

        return $route;
    }

    /**
     * Return list of HTTP verbs route must handle.
     */
    public function getVerbs(): array
    {
        return $this->verbs;
    }
}
