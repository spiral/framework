<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Spiral\Annotations\AnnotatedMethod;
use Spiral\Annotations\AnnotationLocator;
use Spiral\Router\Annotation\Route as RouteAnnotation;

final class RouteLocator
{
    /** @var AnnotationLocator */
    private $locator;

    /**
     * @param AnnotationLocator $locator
     */
    public function __construct(AnnotationLocator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @return array
     */
    public function findDeclarations(): array
    {
        $routes = iterator_to_array($this->locator->findMethods(RouteAnnotation::class));
        uasort($routes, static function (AnnotatedMethod $route1, AnnotatedMethod $route2) {
            return $route1->getAnnotation()->priority <=> $route2->getAnnotation()->priority;
        });

        $result = [];
        foreach ($routes as $match) {
            /** @var RouteAnnotation $route */
            $route = $match->getAnnotation();
            $routeName = $route->name ?? $this->generateName($route);

            $result[$routeName] = [
                'pattern'    => $route->route,
                'controller' => $match->getClass()->getName(),
                'action'     => $match->getMethod()->getName(),
                'group'      => $route->group,
                'verbs'      => (array) $route->methods,
                'defaults'   => $route->defaults,
                'middleware' => (array) $route->middleware,
            ];
        }

        return $result;
    }

    /**
     * Generates route name based on declared methods and route.
     *
     * @param RouteAnnotation $route
     * @return string
     */
    private function generateName(RouteAnnotation $route): string
    {
        $methods = is_array($route->methods)
            ? implode(',', $route->methods)
            : $route->methods;

        return mb_strtolower(sprintf('%s:%s', $methods, $route->route));
    }
}
