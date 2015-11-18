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
use Psr\Log\LoggerAwareInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\DispatcherInterface;
use Spiral\Debug\SnapshotInterface;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Exceptions\ClientExceptions\ServerErrorException;
use Spiral\Http\Exceptions\HttpException;
use Spiral\Http\Routing\Traits\RouterTrait;
use Spiral\Http\Traits\JsonTrait;
use Spiral\Views\ViewsInterface;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Basic spiral Http Dispatcher implementation. Used for web based applications and can route
 * requests to controllers or custom endpoints.
 */
class HttpDispatcher extends HttpCore implements
    DispatcherInterface,
    SingletonInterface,
    LoggerAwareInterface
{
    /**
     * HttpDispatcher has embedded router and log it's errors.
     */
    use RouterTrait, LoggerTrait, JsonTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Format to be used for log messages in cases where http error caused by client request.
     */
    const LOGS = "{scheme}://{host}{path} caused the error {code} ({message}) by client {remote}.";

    /**
     * @var HttpConfig
     */
    protected $config = null;

    /**
     * Required to render error pages.
     *
     * @var ViewsInterface
     */
    protected $views = null;

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
     * Views instance will be requested on demand (error) via container, method used to manually
     * specify it.
     *
     * @param ViewsInterface $views
     * @return $this
     */
    public function setViews(ViewsInterface $views)
    {
        $this->views = $views;

        return $this;
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
        $response = $this->perform($this->request(), $this->response(), $this->endpoint());

        if (!empty($response)) {
            //Sending to client
            $this->dispatch($response);
        }
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
     * @throws \Exception Depends on request isolation.
     */
    public function perform(
        ServerRequestInterface $request,
        ResponseInterface $response = null,
        callable $endpoint = null
    ) {
        try {
            return parent::perform($request, $response, $endpoint);
        } catch (ClientException $exception) {
            return $this->clientException($request, $response, $exception);
        } catch (\Exception $exception) {
            /**
             * Potentially has to be dedicated to specific service.
             *
             * @var SnapshotInterface $snapshot
             */
            $snapshot = $this->container->construct(SnapshotInterface::class, compact('exception'));

            //Snapshot must report about itself
            $snapshot->report();

            return $this->handleSnapshot($snapshot, false, $request, $response);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param bool                   $dispatch Snapshot will be automatically dispatched.
     * @param ServerRequestInterface $request  Request caused snapshot.
     * @param ResponseInterface      $response Response to write anwer into.
     * @return ResponseInterface|null Depends of dispatching were requested.
     */
    public function handleSnapshot(
        SnapshotInterface $snapshot,
        $dispatch = true,
        ServerRequestInterface $request = null,
        ResponseInterface $response = null
    ) {
        if (empty($request)) {
            //Somewhere outside of dispatcher
            $request = $this->request();
        }

        if (empty($response)) {
            $response = $this->response();
        }

        if (!$this->config->exposeErrors()) {
            //Http was not allowed to show any error snapshot to client
            $response = $this->writeException($request, $response, new ServerErrorException());
        } else {
            //Exposing exception
            if ($request->getHeaderLine('Accept') == 'application/json') {
                $response = $this->writeJson(
                    $response,
                    $snapshot->describe(),
                    ClientException::ERROR
                );
            } else {
                $response->getBody()->write($snapshot->render());
                $response = $response->withStatus(ClientException::ERROR);
            }
        }

        if (!$dispatch) {
            return $response;
        }

        return $this->dispatch($response);
    }

    /**
     * Handle ClientException.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param ClientException        $exception
     * @return ResponseInterface
     */
    protected function clientException(
        ServerRequestInterface $request,
        ResponseInterface $response,
        ClientException $exception
    ) {
        //Logging client error
        $this->logError($request, $exception);

        return $this->writeException($request, $response, $exception);
    }

    /**
     * Get initial request instance or create new one.
     *
     * @return ServerRequestInterface
     */
    protected function request()
    {
        //Zend code is here
        $request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

        return $request->withAttribute('basePath', $this->basePath());
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

    /**
     * Create response for specifier error code, some responses can be have associated view files.
     *
     * @param ClientException        $exception
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    private function writeException(
        ServerRequestInterface $request,
        ResponseInterface $response,
        ClientException $exception
    ) {
        if ($request->getHeaderLine('Accept') == 'application/json') {
            $response = $this->writeJson($response, ['status' => $exception->getCode()]);
        }

        if ($this->config->hasView($exception->getCode())) {
            $errorView = $this->views()->render($this->config->errorView($exception->getCode()), [
                'http'    => $this,
                'request' => $request
            ]);

            $response->getBody()->write($errorView);
        }

        return $response->withStatus($exception->getCode());
    }

    /**
     * Get associated views component or fetch it from container.
     *
     * @return ViewsInterface
     */
    private function views()
    {
        if (!empty($this->views)) {
            return $this->views;
        }

        return $this->views = $this->container->get(ViewsInterface::class);
    }

    /**
     * Add error to http log.
     *
     * @param ServerRequestInterface $request
     * @param ClientException        $exception
     */
    private function logError(ServerRequestInterface $request, ClientException $exception)
    {
        $remoteAddress = '-undefined-';
        if (!empty($request->getServerParams()['REMOTE_ADDR'])) {
            $remoteAddress = $request->getServerParams()['REMOTE_ADDR'];
        }

        $this->logger()->warning(static::LOGS, [
            'scheme'  => $request->getUri()->getScheme(),
            'host'    => $request->getUri()->getHost(),
            'path'    => $request->getUri()->getPath(),
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage() ?: '-not specified-',
            'remote'  => $remoteAddress
        ]);
    }
}
