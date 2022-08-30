<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\CallableHandler;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\Exception\TargetException;
use Spiral\Router\Traits\PipelineTrait;

/**
 * Default route provides ability to route request to a given callable handler.
 *
 * Examples:
 *
 * new Route("/login", function(){
 *   return "hello";
 * });
 * new Route("/login", new Handler());
 * new Route("/login", \App\Handlers\Handler::class);
 *
 * new Route("/login", new Action(\App\Controllers|HomeController::class, "login");
 * new Route("/<controller>/<action>/<id>", new Namespaced("\App\Controllers");
 * new Route("/signup/<action>", new Controller(\App\Controllers\SignupController::class);
 * new Route("://<domain>/info", new Action(\App\Controllers|ProfileController::class, "info");
 * new Route("/<controller>/<action>/<id>", new Group(["profile" =>
 * \App\Controllers|ProfileController::class]);
 */
final class Route extends AbstractRoute implements ContainerizedInterface
{
    use PipelineTrait;

    public const ROUTE_ATTRIBUTE = 'route';

    /** @var string|callable|RequestHandlerInterface|TargetInterface */
    private $target;

    /** @var RequestHandlerInterface */
    private $requestHandler;

    /**
     * @param string                                                  $pattern  Uri pattern.
     * @param string|callable|RequestHandlerInterface|TargetInterface $target   Callable route target.
     * @param array                                                   $defaults Default value set.
     */
    public function __construct(string $pattern, $target, array $defaults = [])
    {
        if ($target instanceof TargetInterface) {
            parent::__construct($pattern, array_merge($target->getDefaults(), $defaults));
        } else {
            parent::__construct($pattern, $defaults);
        }

        $this->target = $target;
    }

    public function withUriHandler(UriHandler $uriHandler): RouteInterface
    {
        $route = parent::withUriHandler($uriHandler);
        if ($this->target instanceof TargetInterface) {
            $route->uriHandler = $route->uriHandler->withConstrains(
                $this->target->getConstrains(),
                $this->defaults
            );
        }

        return $route;
    }

    /**
     * Associated route with given container.
     *
     * @return self
     */
    public function withContainer(ContainerInterface $container): ContainerizedInterface
    {
        $route = clone $this;
        $route->container = $container;

        if ($route->target instanceof TargetInterface) {
            $route->target = clone $route->target;
        }

        $route->pipeline = $route->makePipeline();

        return $route;
    }

    /**
     *
     * @throws RouteException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->requestHandler)) {
            $this->requestHandler = $this->requestHandler();
        }

        return $this->pipeline->process(
            $request->withAttribute(self::ROUTE_ATTRIBUTE, $this),
            $this->requestHandler
        );
    }

    /**
     * @throws RouteException
     */
    protected function requestHandler(): RequestHandlerInterface
    {
        if (!$this->hasContainer()) {
            throw new RouteException('Unable to configure route pipeline without associated container');
        }

        if ($this->target instanceof TargetInterface) {
            try {
                return $this->target->getHandler($this->container, $this->matches);
            } catch (TargetException $e) {
                throw new RouteException('Invalid target resolution', $e->getCode(), $e);
            }
        }

        if ($this->target instanceof RequestHandlerInterface) {
            return $this->target;
        }

        try {
            if (is_object($this->target) || is_array($this->target)) {
                $target = $this->target;
            } else {
                $target = $this->container->get($this->target);
            }

            if ($target instanceof RequestHandlerInterface) {
                return $target;
            }

            return new CallableHandler(
                $target,
                $this->container->get(ResponseFactoryInterface::class)
            );
        } catch (ContainerExceptionInterface $e) {
            throw new RouteException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
