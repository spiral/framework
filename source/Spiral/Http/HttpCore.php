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
use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Http\Exceptions\HttpException;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;

/**
 * Magically simple implementation of PRS7 Http core.
 */
class HttpCore extends Component implements HttpInterface
{
    /**
     * HttpDispatcher has embedded router and log it's errors.
     */
    use BenchmarkTrait;

    /**
     * @var EmitterInterface
     */
    private $emitter = null;

    /**
     * Dispatcher endpoint.
     *
     * @var string|callable|null
     */
    private $endpoint = null;

    /**
     * Set of middlewares to be applied for every request.
     *
     * @var callable[]|MiddlewareInterface[]
     */
    protected $middlewares = [];

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ContainerInterface   $container
     * @param array                $middlewares
     * @param callable|null|string $endpoint
     */
    public function __construct(
        ContainerInterface $container,
        array $middlewares = [],
        callable $endpoint = null
    ) {
        $this->container = $container;
        $this->middlewares = $middlewares;

        $this->endpoint = $endpoint;
    }

    /**
     * @param EmitterInterface $emitter
     */
    public function setEmitter(EmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * Set endpoint as callable function or invokable class name (will be resolved using container).
     *
     * @param callable $endpoint
     * @return $this
     */
    public function setEndpoint(callable $endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Add new middleware into chain.
     *
     * Example (in bootstrap):
     * $this->http->middleware(new ProxyMiddleware());
     *
     * @param callable|MiddlewareInterface $middleware
     * @return $this
     */
    public function addMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Pass request thought all http middlewares to appropriate endpoint. Default endpoint will be
     * used as fallback. Can thrown an exception happen in internal code.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $endpoint User specified endpoint.
     * @return ResponseInterface
     * @throws HttpException
     */
    public function perform(
        ServerRequestInterface $request,
        ResponseInterface $response = null,
        callable $endpoint = null
    ) {
        $endpoint = !empty($endpoint) ? $endpoint : $this->endpoint();
        $response = !empty($response) ? $response : $this->response();

        if (empty($endpoint)) {
            throw new HttpException("Unable to execute request without destination endpoint.");
        }

        $pipeline = new MiddlewarePipeline($this->middlewares, $this->container);

        $benchmark = $this->benchmark('request', $request->getUri());
        try {
            //Exceptions (including client one) must be handled by pipeline
            return $pipeline->target($endpoint)->run($request, $response);
        } finally {
            $this->benchmark($benchmark);
        }
    }

    /**
     * Dispatch response to client.
     *
     * @param ResponseInterface $response
     * @return null Specifically.
     */
    public function dispatch(ResponseInterface $response)
    {
        if (empty($this->emitter)) {
            $this->emitter = new SapiEmitter();
        }

        $this->emitter->emit($response, ob_get_level());

        return null;
    }

    /**
     * Create instance of initial response.
     *
     * @return ResponseInterface
     */
    protected function response()
    {
        return new Response('php://memory');
    }

    /**
     * Default endpoint.
     *
     * @return callable|null
     */
    protected function endpoint()
    {
        if (!is_string($this->endpoint)) {
            //Presumably callable
            return $this->endpoint;
        }

        //Specified as class name
        return $this->container->get($this->endpoint);
    }
}