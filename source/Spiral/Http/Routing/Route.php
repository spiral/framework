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
 * {@inheritdoc} General purpose route.
 */
class Route extends AbstractRoute
{
    /**
     * Use this string as your target action to resolve action from routed URL.
     *
     * Example: new Route('name', 'userPanel/<action>', 'Controllers\UserPanel::<action>');
     *
     * Attention, you can't route controllers this way, use DirectRoute for such purposes.
     */
    const DYNAMIC_ACTION = '<action>';

    /**
     * Route target in a form of callable or string pattern.
     *
     * @var callable|string
     */
    protected $target = null;

    /**
     * New Route instance.
     *
     * @param string $name
     * @param string $pattern
     * @param string|callable $target Route target.
     * @param array $defaults
     */
    public function __construct($name, $pattern, $target, array $defaults = [])
    {
        $this->name = $name;
        $this->pattern = $pattern;
        $this->target = $target;
        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
     */
    protected function createEndpoint(ContainerInterface $container)
    {
        if (is_object($this->target) || is_array($this->target)) {
            return $this->target;
        }

        if (is_string($this->target) && strpos($this->target, self::SEPARATOR) === false) {
            //Endpoint
            return $container->get($this->target);
        }

        $route = $this;

        return function () use ($container, $route) {
            list($controller, $action) = explode(self::SEPARATOR, $route->target);

            if ($action == self::DYNAMIC_ACTION) {
                $action = $route->matches['action'];
            }

            return $route->callAction($container, $controller, $action, $route->matches);
        };
    }
}