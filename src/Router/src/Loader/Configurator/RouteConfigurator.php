<?php

declare(strict_types=1);

namespace Spiral\Router\Loader\Configurator;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Router\Exception\TargetException;
use Spiral\Router\RouteCollection;
use Spiral\Router\Target\Action;
use Spiral\Router\Target\Controller;
use Spiral\Router\Target\Group;
use Spiral\Router\Target\Namespaced;
use Spiral\Router\TargetInterface;

final class RouteConfigurator
{
    private array $defaults = [];
    private ?string $group = null;
    private string $prefix = '';
    private ?CoreInterface $core = null;
    private MiddlewareInterface|string|array|null $middleware = null;

    /** @var string|callable|RequestHandlerInterface|TargetInterface */
    private mixed $target = null;

    public function __construct(
        private readonly string $name,
        private readonly string $pattern,
        private readonly RouteCollection $collection
    ) {
    }

    public function __destruct()
    {
        if ($this->target === null) {
            throw new TargetException(
                \sprintf('The [%s] route has no defined target. Call one of: `controller`, `action`, 
                    `namespaced`, `groupControllers`, `callable`, `handler` methods.', $this->name)
            );
        }

        $this->collection->add($this->name, $this);
    }

    /**
     * @internal
     *
     * Don't use this method. For internal use only.
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'core' => $this->core,
            'target' => $this->target,
            'defaults' => $this->defaults,
            'group' => $this->group,
            'middleware' => $this->middleware,
            'pattern' => $this->pattern,
            'prefix' => \trim($this->prefix, '/'),
            default => throw new \BadMethodCallException(\sprintf('Unable to access %s.', $name))
        };
    }

    public function controller(string $controller, int $options = 0, string $defaultAction = 'index'): self
    {
        $this->target = new Controller($controller, $options, $defaultAction);

        return $this;
    }

    public function namespaced(string $namespace, string $postfix = 'Controller', int $options = 0): self
    {
        $this->target = new Namespaced($namespace, $postfix, $options);

        return $this;
    }

    public function groupControllers(array $controllers, int $options = 0, string $defaultAction = 'index'): self
    {
        $this->target = new Group($controllers, $options, $defaultAction);

        return $this;
    }

    public function action(string $controller, string|array $action, int $options = 0): self
    {
        $this->target = new Action($controller, $action, $options);

        return $this;
    }

    public function callable(array|\Closure $callable): self
    {
        $this->target = $callable;

        return $this;
    }

    public function handler(TargetInterface|string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function defaults(array $defaults): self
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function group(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function prefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function core(CoreInterface $core): self
    {
        $this->core = $core;

        return $this;
    }

    public function middleware(MiddlewareInterface|string|array $middleware): self
    {
        $this->middleware = (array) $middleware;

        return $this;
    }
}
