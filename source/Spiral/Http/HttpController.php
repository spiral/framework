<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Core\Controller;
use Spiral\Http\Cookies\CookieManager;
use Spiral\Http\Input\InputManager;

/**
 * Controller with MiddlewarePipeline (PSR-7) functionality added. HttpController actions will
 * always respond using instance of ResponseInterface.
 */
class HttpController extends Controller
{
    /**
     * You can define controller specific middlewares by redefining this property. In default
     * configuration controller defines it's own CookieManager and InputManager
     * middlewares.
     *
     * More middlewares to be added.
     *
     * @var array
     */
    protected $middlewares = [CookieManager::class, InputManager::class];

    /**
     * {@inheritdoc}
     *
     * @param Request $request
     * @param Response $response
     */
    public function callAction(
        $action = '',
        array $parameters = [],
        Request $request = null,
        Response $response = null
    ) {
        if (empty($request)) {
            $request = $this->container->get(Request::class);
        }

        if (empty($response)) {
            $response = $this->container->get(Response::class);
        }

        return parent::callAction($action, $parameters + compact('request', 'response'));
    }

    /**
     * @param \ReflectionMethod $method
     * @param array             $arguments
     * @param array             $parameters
     * @return Response
     */
    protected function executeAction(\ReflectionMethod $method, array $arguments, array $parameters)
    {
        $benchmark = $this->benchmark($method->getName());

        //To invoke method
        $scope = $this;

        try {
            $pipeline = $this->createPipeline($this->actionName($method));

            //Target us our controller method
            $pipeline->target(function () use ($method, $scope, $arguments) {
                //Executing controller method
                return $method->invokeArgs($scope, $arguments);
            });

            //Always provided by callAction
            return $pipeline->run($parameters['request'], $parameters['response']);
        } finally {
            $this->benchmark($benchmark);
        }
    }

    /**
     * Get pipeline for specific action.
     *
     * @param string $action
     * @return MiddlewarePipeline
     */
    protected function createPipeline($action)
    {
        return new MiddlewarePipeline($this->middlewares, $this->container);
    }

    /**
     * Fetch normalized action name from method name.
     *
     * @param \ReflectionMethod $method
     * @return string
     */
    private function actionName(\ReflectionMethod $method)
    {
        return lcfirst(substr(
            $method->getName(),
            strlen(static::ACTION_PREFIX),
            -1 * strlen(static::ACTION_POSTFIX)
        ));
    }
}