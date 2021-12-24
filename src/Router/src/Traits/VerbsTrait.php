<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Traits;

use Spiral\Router\Exception\RouteException;
use Spiral\Router\RouteInterface;

trait VerbsTrait
{
    /** @var array */
    protected $verbs = RouteInterface::VERBS;

    /**
     * Attach specific list of HTTP verbs to the route.
     *
     *
     * @return RouteInterface|$this
     * @throws RouteException
     */
    public function withVerbs(string ...$verbs): RouteInterface
    {
        foreach ($verbs as &$verb) {
            $verb = strtoupper($verb);
            if (!in_array($verb, RouteInterface::VERBS, true)) {
                throw new RouteException("Invalid HTTP verb `{$verb}`");
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
