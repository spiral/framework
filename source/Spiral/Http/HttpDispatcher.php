<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\DispatcherInterface;
use Spiral\Debug\SnapshotInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Exceptions\ClientExceptions\ServerErrorException;
use Spiral\Http\Middlewares\ExceptionIsolator;
use Spiral\Http\Traits\RouterTrait;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Basic spiral Http Dispatcher implementation. Used for web based applications and can route
 * requests to controllers or custom endpoints.
 */
class HttpDispatcher extends HttpCore implements DispatcherInterface, SingletonInterface
{
    /**
     * HttpDispatcher has embedded router and log it's errors.
     */
    use RouterTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * @var HttpConfig
     */
    protected $config = null;

    /**
     * @param HttpConfig         $config
     * @param ContainerInterface $container
     */
    public function __construct(HttpConfig $config, ContainerInterface $container)
    {
        $this->config = $config;

        parent::__construct($container, $config->defaultMiddlewares(), $config->defaultEndpoint());
    }

    /**
     * Application base path.
     *
     * @return string
     */
    public function basePath()
    {
        return $this->config->basePath();
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        //Now we can generate response using request
        $response = $this->perform(
            $this->request(),
            $this->response(),
            $this->endpoint()
        );

        if (!empty($response)) {
            //Sending to client
            $this->dispatch($response);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleSnapshot(SnapshotInterface $snapshot)
    {
        /**
         * Isolator can write exception/snapshot information into given response.
         *
         * @var ExceptionIsolator $isolator
         */
        $isolator = $this->container->get(ExceptionIsolator::class);

        //Somewhere outside of dispatcher
        $request = $this->request();
        $response = $this->response();

        if (!$this->config->exposeErrors()) {
            //Http was not allowed to show any error snapshot to client
            $response = $isolator->writeException($request, $response, new ServerErrorException());
        } else {
            $response = $isolator->writeSnapshot($request, $response, $snapshot);
        }

        $this->dispatch($response);
    }

    /**
     * Get initial request instance or create new one.
     *
     * @return ServerRequestInterface
     */
    protected function request()
    {
        //Zend code is here
        return ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    }

    /**
     * {@inheritdoc}
     */
    protected function response()
    {
        $response = parent::response();
        foreach ($this->config->defaultHeaders() as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function endpoint()
    {
        if (!empty($endpoint = parent::endpoint())) {
            //Endpoint specified by user
            return $endpoint;
        }

        //We are using router as default endpoint
        return $this->router();
    }

    /**
     * {@inheritdoc}
     */
    protected function createRouter()
    {
        return $this->container->construct(
            $this->config->routerClass(),
            $this->config->routerParameters()
        );
    }
}
