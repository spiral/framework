<?php

declare(strict_types=1);

namespace Spiral\Http\Traits;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Provides ability to manage set of middleware.
 */
trait MiddlewareTrait
{
    /**
     * Set of middleware to be applied for every request.
     *
     * @var MiddlewareInterface[]
     */
    protected array $middleware = [];

    /**
     * Add new middleware at the end of chain.
     *
     * Example (in bootstrap):
     * $this->http->pushMiddleware(new ProxyMiddleware());
     */
    public function pushMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Add new middleware at the top chain.
     *
     * Example (in bootstrap):
     * $this->http->riseMiddleware(new ProxyMiddleware());
     */
    public function riseMiddleware(MiddlewareInterface $middleware): self
    {
        \array_unshift($this->middleware, $middleware);

        return $this;
    }
}
