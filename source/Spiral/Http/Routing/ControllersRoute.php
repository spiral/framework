<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Routing;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Http\Routing\Traits\CoreTrait;

/**
 * {@inheritdoc}
 *
 * Used to route to specified namespace of controllers. DirectRoute can route only to controllers,
 * which means that pattern should always include both <controller> and <action> segments.
 *
 * Usually DirectRoute used to create "general" routing without definition of route for every
 * controller action and etc. Having DirectRoute attached to Router as default route will allow
 * user to generate urls based on controller action name ($router->createUri("controller::action")
 * or
 * $router->uri("controller/action")).
 *
 * Examples:
 * new ControllersRoute(
 *      "default",
 *      "[<controller>[/<action>[/<id>]]]",
 *      "Controllers",
 *      "Controller",
 *      ["controller" => "home"]
 * );
 *
 * You can also create host depended routes.
 * $route = new ControllersRoute(
 *      "default",
 *      "domain.com[/<controller>[/<action>[/<id>]]]",
 *      "Controllers",
 *      "Controller",
 *      ["controller" => "home"]
 * );
 * $route->withHost();
 *
 * Attention, controller names are lowercased! If you want to add controller which has multiple
 * words in it's class name - use aliases (last argument).
 */
class ControllersRoute extends AbstractRoute
{
    use CoreTrait;

    /**
     * Default controllers namespace.
     *
     * @var string
     */
    private $namespace = '';

    /**
     * Default controller postfix.
     *
     * @var string
     */
    private $postfix = '';

    /**
     * Controllers aliased by name, namespace and postfix will be ignored in this case.
     *
     * @var array
     */
    private $controllers = [];

    /**
     * New instance of DirectRoute.
     *
     * @param string $name
     * @param string $pattern
     * @param string $namespace   Default controllers namespace.
     * @param string $postfix     Default controller postfix.
     * @param array  $defaults    Default values (including default controller).
     * @param array  $controllers Controllers aliased by their name, namespace and postfix will be
     *                            ignored in this case.
     */
    public function __construct(
        $name,
        $pattern,
        $namespace,
        $postfix = 'Controller',
        array $defaults = [],
        array $controllers = []
    ) {
        parent::__construct($name, $defaults);

        $this->pattern = $pattern;
        $this->namespace = $namespace;
        $this->postfix = $postfix;
        $this->controllers = $controllers;
    }

    /**
     * Create controller aliases, namespace and postfix will be ignored in this case.
     * Example: $route->withControllers([
     *      "auth" => "Module\Authorization\AuthController"
     * ]);
     *
     * @param array $controllers
     * @return $this
     */
    public function withControllers(array $controllers)
    {
        $route = clone $this;
        $route->controllers += $controllers;

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    protected function createEndpoint()
    {
        $route = $this;

        return function () use ($route) {
            $matches = $route->getMatches();

            //Due we are expecting part of class name we can remove some garbage (see to-do below)
            $controller = strtolower(preg_replace('/[^a-z_0-9]+/i', '', $matches['controller']));

            if (isset($route->controllers[$controller])) {
                //Aliased
                $controller = $route->controllers[$controller];
            } else {
                $controller = Inflector::classify($controller) . $route->postfix;
                $controller = "{$route->namespace}\\{$controller}";
            }

            return $route->callAction($controller, $matches['action'], $matches);
        };
    }
}