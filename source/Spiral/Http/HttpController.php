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
use Spiral\Core\ContainerInterface;
use Spiral\Core\Controller;
use Spiral\Http\Cookies\CookieManager;

/**
 * Controller with MiddlewarePipeline (PSR-7) functionality added. HttpController actions will
 * always respond using instance of ResponseInterface.
 */
class HttpController extends Controller
{
    /**
     * @var MiddlewarePipeline
     */
    private $pipeline = null;

    /**
     * As moment of controller initialization.
     *
     * @var ResponseInterface
     */
    private $request = null;

    /**
     * As moment of controller initialization.
     *
     * @var ResponseInterface
     */
    private $response = null;

    /**
     * You can define controller specific middlewares by redefining this property. In default
     * configuration controller defines it's own CookieManager middleware.
     *
     * More middlewares to be added.
     *
     * @var array
     */
    protected $middlewares = [
        CookieManager::class
    ];

    /**
     * @param ContainerInterface     $container
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function __construct(
        ContainerInterface $container,
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        parent::__construct($container);

        $this->request = $request;
        $this->response = $response;

        $this->pipeline = new MiddlewarePipeline($this->container, $this->middlewares);
    }

    /**
     * Set Request to be used as initial in internal MiddlewarePipeline.
     *
     * @param ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Set Response to be used as initial in internal MiddlewarePipeline.
     *
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Set controller middleware pipeline.
     *
     * @param MiddlewarePipeline $pipeline
     */
    public function setPipeline(MiddlewarePipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * Add middleware to active pipeline.
     *
     * @param callable $middleware
     */
    protected function addMiddleware($middleware)
    {
        $this->pipeline->add($middleware);
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
            return $this->pipeline->target(function () use ($method, $scope, $arguments) {
                //Executing controller method
                return $method->invokeArgs($scope, $arguments);
            })->run($this->request, $this->response);
        } finally {
            $this->benchmark($benchmark);
        }
    }
}