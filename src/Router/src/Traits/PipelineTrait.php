<?php

declare(strict_types=1);

namespace Spiral\Router\Traits;

use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Http\Pipeline;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\PipelineFactory;
use Spiral\Router\RouteInterface;

/**
 * @psalm-type MiddlewareType = MiddlewareInterface|class-string<MiddlewareInterface>|non-empty-string|Autowire
 */
trait PipelineTrait
{
    use ContainerTrait;

    protected ?Pipeline $pipeline = null;

    /** @psalm-var array<array-key, MiddlewareType> */
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
     * @param MiddlewareType|array{0:MiddlewareType[]} ...$middleware
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

        /** @var MiddlewareType[] $middleware */
        foreach ($middleware as $item) {
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
        \assert($this->container !== null);
        try {
            return $this->container
                ->get(PipelineFactory::class)
                ->createWithMiddleware($this->middleware);
        } catch (ContainerExceptionInterface $e) {
            throw new RouteException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
