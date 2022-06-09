<?php

declare(strict_types=1);

namespace Spiral\Router\Traits;

use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\Http\Pipeline;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\RouteInterface;

trait PipelineTrait
{
    use ContainerTrait;

    protected ?Pipeline $pipeline = null;

    /** @psalm-var array<array-key, class-string<MiddlewareInterface>>|MiddlewareInterface[] */
    protected array $middleware = [];

    /**
     * Associated middleware with route. New instance of route will be returned.
     *
     * Example:
     * $route->withMiddleware(new CacheMiddleware(100));
     * $route->withMiddleware(ProxyMiddleware::class);
     * $route->withMiddleware(ProxyMiddleware::class, OtherMiddleware::class);
     * $route->withMiddleware([ProxyMiddleware::class, OtherMiddleware::class]);
     *
     * @param MiddlewareInterface|string|array ...$middleware
     * @return RouteInterface|$this
     *
     * @throws RouteException
     */
    public function withMiddleware(...$middleware): RouteInterface
    {
        $route = clone $this;

        // array fallback
        if (\count($middleware) === 1 && \is_array($middleware[0])) {
            $middleware = $middleware[0];
        }

        foreach ($middleware as $item) {
            if (!\is_string($item) && !$item instanceof MiddlewareInterface) {
                $name = get_debug_type($item);

                throw new RouteException(\sprintf('Invalid middleware `%s`', $name));
            }

            $route->middleware[] = $item;
        }

        if ($route->pipeline !== null) {
            $route->pipeline = $route->makePipeline();
        }

        return $route;
    }

    public function withPipeline(Pipeline $pipeline): static
    {
        $route = clone $this;

        $route->middleware = [$pipeline];
        $route->pipeline = $pipeline;

        return $route;
    }

    /**
     * Get associated route pipeline.
     *
     * @throws RouteException
     */
    protected function makePipeline(): Pipeline
    {
        // pre-aggregated
        if (\count($this->middleware) === 1 && $this->middleware[0] instanceof Pipeline) {
            return $this->middleware[0];
        }

        try {
            $pipeline = $this->container->get(Pipeline::class);

            foreach ($this->middleware as $middleware) {
                if ($middleware instanceof MiddlewareInterface) {
                    $pipeline->pushMiddleware($middleware);
                } else {
                    // dynamically resolved
                    $pipeline->pushMiddleware($this->container->get($middleware));
                }
            }
        } catch (ContainerExceptionInterface $e) {
            throw new RouteException($e->getMessage(), $e->getCode(), $e);
        }

        return $pipeline;
    }
}
