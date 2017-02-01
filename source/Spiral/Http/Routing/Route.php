<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Http\Routing;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Http\Routing\Traits\CoreTrait;

/**
 * {@inheritdoc} General purpose route.
 */
class Route extends AbstractRoute
{
    use CoreTrait;

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
     * @param string          $name
     * @param string          $pattern
     * @param string|callable $target Route target. Can be in a form of controler:action
     * @param array           $defaults
     */
    public function __construct(string $name, string $pattern, $target, array $defaults = [])
    {
        parent::__construct($name, $defaults);

        $this->pattern = ltrim($pattern, '/');
        $this->target = $target;
    }

    /**
     * {@inheritdoc}
     */
    protected function createEndpoint()
    {
        if (is_object($this->target) || is_array($this->target)) {
            return $this->target;
        }

        if (is_string($this->target) && strpos($this->target, ':') === false) {
            //Endpoint specified as string
            return $this->iocContainer()->get($this->target);
        }

        $route = $this;

        return function (Request $request, Response $response) use ($route) {
            list($controller, $action) = explode(':', str_replace('::', ':', $route->target));

            if ($action == self::DYNAMIC_ACTION) {
                $action = $route->getMatches()['action'];
            }

            //Calling action with matched parameters and request/response scope
            return $route->callAction(
                $controller,
                $action,
                $route->getMatches(),
                [Request::class => $request, Response::class => $response]
            );
        };
    }
}