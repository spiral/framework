<?php

declare(strict_types=1);

namespace Spiral\Router;

use Spiral\Attributes\ReaderInterface;
use Spiral\Router\Annotation\Route;
use Spiral\Tokenizer\ScopedClassesInterface;

final class RouteLocator
{
    public function __construct(
        private readonly ScopedClassesInterface $locator,
        private readonly ReaderInterface $reader
    ) {
    }

    /**
     * @return array<string, array<string, array|int|string>>
     *
     * @psalm-type _AssocArray = array{
     *    pattern: string,
     *    controller: class-string,
     *    action: string,
     *    group: string,
     *    verbs: array,
     *    defaults: array,
     *    middleware: array,
     *    priority: int
     * }
     * @psalm-return array<string, _AssocArray>
     */
    public function findDeclarations(): array
    {
        $result = [];
        foreach ($this->locator->getScopedClasses('routes') as $class) {
            foreach ($class->getMethods() as $method) {
                $route = $this->reader->firstFunctionMetadata($method, Route::class);

                if ($route === null) {
                    continue;
                }

                $result[$route->name ?? $this->generateName($route)] = [
                    'pattern'    => $route->route,
                    'controller' => $class->getName(),
                    'action'     => $method->getName(),
                    'group'      => $route->group,
                    'verbs'      => (array) $route->methods,
                    'defaults'   => $route->defaults,
                    'middleware' => (array) $route->middleware,
                    'priority'   => $route->priority,
                ];
            }
        }

        \uasort($result, static fn (array $route1, array $route2) => $route1['priority'] <=> $route2['priority']);

        return $result;
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
