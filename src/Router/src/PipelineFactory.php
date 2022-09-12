<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Http\Pipeline;
use Spiral\Router\Exception\RouteException;

final class PipelineFactory
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly FactoryInterface $factory
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

        $pipeline = $this->factory->make(Pipeline::class);
        \assert($pipeline instanceof Pipeline);

        foreach ($middleware as $item) {
            if ($item instanceof MiddlewareInterface) {
                $pipeline->pushMiddleware($item);
            } elseif (\is_string($item)) {
                $item = $this->container->get($item);
                \assert($item instanceof MiddlewareInterface);

                $pipeline->pushMiddleware($item);
            } elseif ($item instanceof Autowire) {
                $item = $item->resolve($this->factory);
                \assert($item instanceof MiddlewareInterface);

                $pipeline->pushMiddleware($item);
            } else {
                $name = get_debug_type($item);
                throw new RouteException(\sprintf('Invalid middleware `%s`', $name));
            }
        }

        return $pipeline;
    }
}
