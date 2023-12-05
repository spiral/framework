<?php

declare(strict_types=1);

namespace Spiral\Router;

use ReflectionClass;
use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;
use Spiral\Router\Annotation\Route;
use Spiral\Router\Target\Action;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(Route::class, useAnnotations: true)]
final class RouteLocatorListener implements TokenizationListenerInterface
{
    /** @var array<array-key, array{ReflectionMethod, Route}> */
    private array $attributes = [];

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly GroupRegistry $groups,
    ) {
    }

    public function listen(ReflectionClass $class): void
    {
        foreach ($class->getMethods() as $method) {
            $route = $this->reader->firstFunctionMetadata($method, Route::class);

            if ($route === null) {
                continue;
            }

            $this->attributes[] = [$method, $route];
        }
    }

    public function finalize(): void
    {
        $defaultGroup = $this->groups->getDefaultGroup();

        $routes = [];
        foreach ($this->attributes as $classes) {
            [$method, $route] = $classes;
            $class = $method->getDeclaringClass();

            $routes[$route->name ?? $this->generateName($route)] = [
                'pattern' => $route->route,
                'controller' => $class->getName(),
                'action' => $method->getName(),
                'group' => $route->group ?? $defaultGroup,
                'verbs' => (array)$route->methods,
                'defaults' => $route->defaults,
                'middleware' => $route->middleware,
                'priority' => $route->priority,
            ];
        }

        \uasort($routes, static fn (array $route1, array $route2) => $route1['priority'] <=> $route2['priority']);

        $this->configureRoutes($routes);
    }

    private function configureRoutes(array $routes): void
    {
        foreach ($routes as $name => $schema) {
            $route = new \Spiral\Router\Route(
                $schema['pattern'],
                new Action($schema['controller'], $schema['action']),
                $schema['defaults']
            );

            $this->groups
                ->getGroup($schema['group'])
                ->addRoute($name, $route->withVerbs(...$schema['verbs'])->withMiddleware(...$schema['middleware']));
        }
    }

    /**
     * Generates route name based on declared methods and route.
     */
    private function generateName(Route $route): string
    {
        $methods = \is_array($route->methods)
            ? \implode(',', $route->methods)
            : $route->methods;

        return \mb_strtolower(\sprintf('%s:%s', $methods, $route->route));
    }
}
