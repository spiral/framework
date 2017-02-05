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
    public function getRouter(): RouterInterface
    {
        if (empty($this->router)) {
            $this->router = $this->createRouter();
        }

        return $this->router;
    }

    /**
     * Add new route.
     *
     * @param RouteInterface $route
     *
     * @return $this|self
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
     * @return $this|self
     */
    public function defaultRoute(RouteInterface $route)
    {
        $this->getRouter()->defaultRoute($route);

        return $this;
    }

    /**
     * Create router instance using container.
     *
     * @return RouterInterface
     * @throws ScopeException
     */
    abstract protected function createRouter(): RouterInterface;

    /**
     * @return ContainerInterface
     */
    abstract protected function iocContainer();
}
