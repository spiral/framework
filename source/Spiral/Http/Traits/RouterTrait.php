<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Traits;

use Spiral\Core\Exceptions\SugarException;
use Spiral\Core\InteropContainerInterface;
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
    public function router()
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
     * @return $this
     */
    public function addRoute(RouteInterface $route)
    {
        $this->router()->addRoute($route);

        return $this;
    }

    /**
     * Default route is needed as fallback if no other route matched the request.
     *
     * @param RouteInterface $route
     * @return RouteInterface
     */
    public function defaultRoute(RouteInterface $route)
    {
        $this->router()->defaultRoute($route);

        return $route;
    }

    /**
     * Create router instance using container.
     *
     * @todo make abstract
     * @return RouterInterface
     * @throws SugarException
     */
    protected function createRouter()
    {
        if (empty($container = $this->container()) || !$container->has(RouterInterface::class)) {
            throw new SugarException(
                "Unable to create Router, container not set or binding is missing."
            );
        }

        //Let's create default router
        return $container->get(RouterInterface::class);
    }

    /**
     * @return InteropContainerInterface
     */
    abstract protected function container();
}
