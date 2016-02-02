<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Routing;

use Spiral\Core\ContainerInterface;

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
 * $router->createUri("controller/action")).
 *
 * Examples:
 * new DirectRoute(
 *      "default",
 *      "[<controller>[/<action>[/<id>]]]",
 *      "Controllers",
 *      "Controller",
 *      ["controller" => "home"]
 * );
 *
 * You can also create host depended routes.
 * $route = new DirectRoute(
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
        $this->name = $name;
        $this->pattern = $pattern;
        $this->namespace = $namespace;
        $this->postfix = $postfix;
        $this->defaults = $defaults;
        $this->controllers = $controllers;
    }

    /**
     * Create controller aliases, namespace and postfix will be ignored in this case.
     * Example: $route->controllers(["auth" => "Module\Authorization\AuthController"]);
     *
     * @todo immutable
     * @param array $controllers
     * @return $this
     */
    public function controllers(array $controllers)
    {
        $this->controllers += $controllers;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function createEndpoint(ContainerInterface $container)
    {
        $route = $this;

        return function () use ($container, $route) {
            $controller = $route->matches['controller'];

            //Due we are expecting part of class name we can remove some garbage (see to-do below)
            $controller = strtolower(preg_replace('/[^a-z_0-9]+/i', '', $controller));

            if (isset($route->controllers[$controller])) {
                //Aliased
                $controller = $route->controllers[$controller];
            } else {
                //todo: Use better logic, maybe Doctrine Inflector (maybe class-name style)
                $controller = $route->namespace . '\\' . (ucfirst($controller) . $route->postfix);
            }

            return $route->callAction(
                $container,
                $controller,
                $route->matches['action'],
                $route->matches
            );
        };
    }
}