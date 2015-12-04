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
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\DispatcherInterface;
use Spiral\Debug\SnapshotInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Traits\JsonTrait;
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
    use RouterTrait, JsonTrait;

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
        //Somewhere outside of dispatcher
        $request = $this->request();
        $response = $this->response();

        $this->dispatch(
            $this->writeSnapshot($request, $response, $snapshot)
        );
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
        return $this->container->make(
            $this->config->routerClass(),
            $this->config->routerParameters()
        );
    }

    /**
     * Write snapshot content into exception.
     *
     * @param Request           $request
     * @param Response          $response
     * @param SnapshotInterface $snapshot
     * @return Response
     */
    private function writeSnapshot(
        Request $request,
        Response $response,
        SnapshotInterface $snapshot
    ) {
        //Exposing exception
        if ($request->getHeaderLine('Accept') != 'application/json') {
            $response->getBody()->write($snapshot->render());

            //Normal exception page
            return $response->withStatus(ClientException::ERROR);
        }

        //Exception in a form of JSON object
        return $this->writeJson($response, $snapshot->describe(), ClientException::ERROR);
    }
}
