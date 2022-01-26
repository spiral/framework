<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Spiral\Attributes\ReaderInterface;
use Spiral\Router\Annotation\Route;
use Spiral\Tokenizer\ClassLocator;

final class RouteLocator
{
    /** @var ClassLocator */
    private $locator;

    /** @var ReaderInterface */
    private $reader;

    public function __construct(ClassLocator $locator, ReaderInterface $reader)
    {
        $this->locator = $locator;
        $this->reader = $reader;
    }

    public function findDeclarations(): array
    {
        $result = [];
        foreach ($this->locator->getClasses() as $class) {
            foreach ($class->getMethods() as $method) {
                $route = $this->reader->firstFunctionMetadata($method, Route::class);

                if ($route === null) {
                    continue;
                }

                $result[$route->name] = [
                    'pattern'    => $route->route,
                    'controller' => $class->getName(),
                    'action'     => $method->getName(),
                    'group'      => $route->group,
                    'verbs'      => (array) $route->methods,
                    'defaults'   => $route->defaults,
                    'middleware' => (array) $route->middleware,
                ];
            }
        }

        return $result;
    }
}
