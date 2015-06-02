<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Router;

use Spiral\Core\Container;

trait RouterTrait
{
    /**
     * Container is required to correctly construct Router and etc.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * Set of pre-defined routes to be send to Router and passed thought request later.
     *
     * @var RouteInterface[]
     */
    protected $routes = array();

    /**
     * Router middleware used by HttpDispatcher and modules to perform URI based routing with defined
     * endpoint such as controller action, closure or middleware.
     *
     * @var Router
     */
    protected $router = null;

    /**
     * Get Router instance.
     *
     * @return Router
     */
    public function getRouter()
    {
        if (!empty($this->router))
        {
            return $this->router;
        }

        return $this->router = $this->createRouter();
    }

    /**
     * Create router instance with aggregated routes.
     *
     * @return Router
     */
    protected function createRouter()
    {
        return Router::make(array(
            'container' => $this->container,
            'routes'    => $this->routes
        ), $this->container);
    }
}