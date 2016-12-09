<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Http\Traits;

use Spiral\Http\MiddlewareInterface;

/**
 * Provides ability to manage set of middlewares.
 */
trait MiddlewaresTrait
{
    /**
     * Set of middlewares to be applied for every request.
     *
     * @var callable[]|MiddlewareInterface[]
     */
    protected $middlewares = [];

    /**
     * Add new middleware at the end of chain.
     *
     * Example (in bootstrap):
     * $this->http->pushMiddleware(new ProxyMiddleware());
     *
     * @param callable|MiddlewareInterface $middleware
     * @return $this
     */
    public function pushMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Add new middleware to the top chain.
     *
     * Example (in bootstrap):
     * $this->http->riseMiddleware(new ProxyMiddleware());
     *
     * @param callable|MiddlewareInterface $middleware
     * @return $this
     */
    public function riseMiddleware($middleware)
    {
        array_unshift($this->middlewares, $middleware);

        return $this;
    }
}