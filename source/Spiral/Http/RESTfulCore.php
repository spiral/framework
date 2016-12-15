<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\HMVC\CoreInterface;
use Spiral\Http\Exceptions\RESTfulException;

/**
 * Default core wrapper which provides ability to alter action name with request specific method.
 */
class RESTfulCore implements CoreInterface, SingletonInterface
{
    /**
     * Application core.
     *
     * @var CoreInterface
     */
    private $core;

    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

    /**
     * {@inheritdoc}
     */
    public function callAction(
        string $controller,
        string $action = null,
        array $parameters = [],
        array $scope = []
    ) {
        if (empty($scope[Request::class])) {
            throw new RESTfulException(
                "RESTful core can only work in a proper http core, Request class is missing"
            );
        }

        if (!$scope[Request::class] instanceof Request) {
            throw new RESTfulException(
                "RESTful core can only work in a proper http core, invalid Request scope"
            );
        }

        return $this->core->callAction(
            $controller,
            $this->defineAction($scope[Request::class], $parameters, $action),
            $parameters,
            $scope
        );
    }

    /**
     * Define action name based on a given request method.
     *
     * @param Request $request
     * @param array   $parameters
     * @param string  $action
     *
     * @return string
     */
    protected function defineAction(Request $request, array $parameters, string $action)
    {
        //methodAction [putPost, getPost]
        return strtolower($request->getMethod()) . ucfirst($action);
    }
}