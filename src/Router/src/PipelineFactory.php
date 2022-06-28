<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Http\Pipeline;
use Spiral\Router\Exception\RouteException;

final class PipelineFactory
{
    public function __construct(
        private readonly Container $container
    ) {
    }

    /**
     * @throws RouteException
     * @throws ContainerExceptionInterface
     */
    public function createWithMiddleware(array $middleware): Pipeline
    {
        if (\count($middleware) === 1 && $middleware[0] instanceof Pipeline) {
            return $middleware[0];
        }

        $pipeline = $this->container->get(Pipeline::class);

        foreach ($middleware as $item) {
            if ($item instanceof MiddlewareInterface) {
                $pipeline->pushMiddleware($item);
            } elseif (\is_string($item) || $item instanceof Autowire) {
                $pipeline->pushMiddleware($this->container->get($item));
            } else {
                $name = get_debug_type($item);
                throw new RouteException(\sprintf('Invalid middleware `%s`', $name));
            }
        }

        return $pipeline;
    }
}
