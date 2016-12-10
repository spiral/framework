<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Traits;

use Spiral\Core\ContainerInterface;
use Spiral\Core\Exceptions\ScopeException;
use Spiral\Http\Routing\RouteInterface;
use Spiral\Http\Routing\RouterInterface;

/**
 * Provides set of method used to create and populate associated router. Can be used inside http
 * dispatcher or custom endpoint implementations.
 *
 * Default router creation requires container to be set!
 */
trait RouterTrait
{
    /**
     * @internal
     * @var RouterInterface|null
     */
    private $router = null;

    /**
     * Set custom router implementation.
     *
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Get associated router or create new one.
     *
     * @return RouterInterface
     */
    public function getRouter()
    {
        if (!empty($this->router)) {
            return $this->router;
        }

        return $this->router = $this->createRouter();
    }

    /**
     * Add new route.
     *
     * @param RouteInterface $route
     *
     * @return $this
     */
    public function addRoute(RouteInterface $route)
    {
        $this->getRouter()->addRoute($route);

        return $this;
    }

    /**
     * Default route is needed as fallback if no other route matched the request.
     *
     * @param RouteInterface $route
     *
     * @return RouteInterface
     */
    public function defaultRoute(RouteInterface $route)
    {
        $this->getRouter()->defaultRoute($route);

        return $route;
    }

    /**
     * Create router instance using container.
     *
     * @return RouterInterface
     * @throws ScopeException
     */
    protected function createRouter()
    {
        $container = $this->iocContainer();
        if (empty($container) || !$container->has(RouterInterface::class)) {
            throw new ScopeException(
                "Unable to create Router, container not set or binding is missing"
            );
        }

        //Let's create default router
        return $container->get(RouterInterface::class);
    }

    /**
     * @return ContainerInterface
     */
    abstract protected function iocContainer();
}
