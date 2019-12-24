<?php

declare(strict_types=1);

namespace Spiral\Command\Router;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Console\Command;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Router\RouterInterface;
use Spiral\Router\Target\Action;
use Spiral\Router\TargetInterface;

class DebugCommand extends Command implements SingletonInterface
{
    protected const NAME = 'debug:router';
    protected const DESCRIPTION = 'List application routes';

    /**
     * @param RouterInterface $router
     */
    public function perform(RouterInterface $router): void
    {
        $routeParamsExtractClosure = function () {
            return new class($this->pattern, $this->target) {
                /** @var string $pattern */
                public $pattern;
                /** @var string|callable|RequestHandlerInterface|TargetInterface $target */
                public $target;

                public function __construct($pattern, $target)
                {
                    $this->target = $target;
                    $this->pattern = $pattern;
                }
            };
        };

        $closureCallable = function (string $method, array $arguments) {
            return $this->{$method}(...$arguments);
        };

        $rows = new ArrayCollection();
        foreach ($this->getRoutes($router) as $route) {
            $extractedParams = $routeParamsExtractClosure->call($route);
            if ($extractedParams->target instanceof Action) {
                $closureCallable->bindTo($extractedParams->target, Action::class);
                $handler = $closureCallable->call($extractedParams->target, 'resolveController', [[]])
                    . '::' .
                    $closureCallable->call($extractedParams->target, 'resolveAction', [[]]);
            } elseif (is_object($extractedParams->target)) {
                $handler = get_class($extractedParams->target);
            } else {
                $handler = $extractedParams->target;
            }
            $rows->add(
                [$extractedParams->pattern, implode(',', $route->getVerbs()), $handler]
            );
        }
        $this->table(['Pattern', 'Verbs', 'Action'], $rows->toArray())->render();
    }

    public function getRoutes(RouterInterface $router): ?\Generator
    {
        yield from $router->getRoutes();
    }
}
