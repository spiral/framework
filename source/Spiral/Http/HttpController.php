<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Controller;
use Spiral\Http\Cookies\CookieManager;
use Spiral\Http\Headers\HeaderManager;
use Spiral\Http\Input\InputManager;

/**
 * Controller with MiddlewarePipeline (PSR-7) functionality added. HttpController actions will
 * always respond using instance of ResponseInterface.
 */
class HttpController extends Controller
{
    /**
     * You can define controller specific middlewares by redefining this property. In default
     * configuration controller defines it's own CookieManager, HeaderManager and InputManager
     * middlewares.
     *
     * More middlewares to be added.
     *
     * @var array
     */
    protected $middlewares = [
        CookieManager::class,
        HeaderManager::class,
        InputManager::class
    ];

    /**
     * {@inheritdoc}
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function callAction(
        $action = '',
        array $parameters = [],
        ServerRequestInterface $request = null,
        ResponseInterface $response = null
    ) {
        if (empty($request)) {
            $request = $this->container->get(ServerRequestInterface::class);
        }

        if (empty($response)) {
            $response = $this->container->get(ResponseInterface::class);
        }

        return parent::callAction($action, $parameters + compact('request', 'response'));
    }

    /**
     * @param \ReflectionMethod $method
     * @param array             $arguments
     * @param array             $parameters
     * @return ResponseInterface
     */
    protected function executeAction(\ReflectionMethod $method, array $arguments, array $parameters)
    {
        $benchmark = $this->benchmark($action = $method->getName());

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