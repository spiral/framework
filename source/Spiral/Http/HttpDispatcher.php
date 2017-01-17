<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\DispatcherInterface;
use Spiral\Debug\SnapshotInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Exceptions\ClientExceptions\ServerErrorException;
use Spiral\Http\Traits\RouterTrait;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Basic spiral Http Dispatcher implementation. Used for web based applications and can route
 * requests to controllers or custom endpoints.
 *
 * HttpDispatcher, it's endpoint can be replaced on application level with any other
 * implementation.
 */
class HttpDispatcher extends HttpCore implements DispatcherInterface, SingletonInterface
{
    use RouterTrait, BenchmarkTrait;

    /**
     * @var HttpConfig
     */
    protected $config = null;

    /**
     * @param HttpConfig         $config
     * @param ContainerInterface $container
     */
    public function __construct(HttpConfig $config, ContainerInterface $container = null)
    {
        $this->config = $config;

        parent::__construct(
            $config->defaultEndpoint(),
            $config->defaultMiddlewares(),
            $container ?? new Container()
        );
    }

    /**
     * Application base path.
     *
     * @return string
     */
    public function basePath(): string
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
            $this->initRequest(),
            $this->initResponse()
        );

        if (!empty($response)) {
            //Sending to client
            $this->dispatch($response);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @todo Use already initiated requests?
     */
    public function handleSnapshot(SnapshotInterface $snapshot)
    {
        if (!$this->config->exposeErrors()) {
            //Standard 500 error page
            $response = $this->errorWriter()->writeException(
                $this->initRequest(),
                $this->initResponse(),
                new ServerErrorException()
            );
        } else {
            //Rendering details about exception
            $response = $this->errorWriter()->writeSnapshot(
                $this->initRequest(),
                $this->initResponse(),
                $snapshot
            );
        }

        $this->dispatch($response);
    }

    /**
     * Get initial request instance or create new one.
     *
     * @return ServerRequestInterface
     */
    protected function initRequest(): ServerRequestInterface
    {
        $benchmark = $this->benchmark('init:request');
        try {
            /**
             * @see \Zend\Diactoros\ServerRequestFactory
             */
            return ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
        } finally {
            $this->benchmark($benchmark);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initResponse(): ResponseInterface
    {
        $benchmark = $this->benchmark('init:response');
        try {
            $response = parent::initResponse();
            foreach ($this->config->defaultHeaders() as $header => $value) {
                $response = $response->withHeader($header, $value);
            }

            return $response;
        } finally {
            $this->benchmark($benchmark);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getEndpoint()
    {
        if (!empty($endpoint = parent::getEndpoint())) {
            //Endpoint specified by user
            return $endpoint;
        }

        //We are using router as default endpoint
        return $this->getRouter();
    }

    /**
     * {@inheritdoc}
     */
    protected function createRouter()
    {
        return $this->container->make(
            $this->config->routerClass(),
            $this->config->routerOptions()
        );
    }

    /**
     * Instance of ErrorWriter.
     *
     * @return ErrorWriter
     */
    protected function errorWriter(): ErrorWriter
    {
        return $this->container->get(ErrorWriter::class);
    }
}