<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Spiral\Annotations\AnnotationLocator;
use Spiral\Router\Annotation\Route as RouteAnnotation;

final class RouteLocator
{
    /** @var AnnotationLocator */
    private $locator;

    public function __construct(AnnotationLocator $locator)
    {
        $this->locator = $locator;
    }

    public function findDeclarations(): array
    {
        $result = [];
        foreach ($this->locator->findMethods(RouteAnnotation::class) as $match) {
            /** @var RouteAnnotation $route */
            $route = $match->getAnnotation();

            $result[$route->name] = [
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
}
