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
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\DispatcherInterface;
use Spiral\Debug\SnapshotInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Exceptions\ClientExceptions\ServerErrorException;
use Spiral\Http\Traits\RouterTrait;
use Spiral\Views\ViewsInterface;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Basic spiral Http Dispatcher implementation. Used for web based applications and can route
 * requests to controllers or custom endpoints.
 *
 * HttpDispatcher, it's endpoing can be replaced on application level with any other
 * implementation.
 */
class HttpDispatcher extends HttpCore implements DispatcherInterface, SingletonInterface
{
    use RouterTrait;

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
            $this->response()
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

        $writer = $this->container->get(ErrorWriter::class);

        if (!$this->config->exposeErrors()) {
            $response = $writer->writeException($request, $response, new ServerErrorException());
        } else {
            $response = $writer->writeSnapshot($request, $response, $snapshot);
        }

        $this->dispatch($response);
    }

    /**
     * Get initial request instance or create new one.
     *
     * @return Request
     */
    protected function request()
    {
        $benchmark = $this->benchmark('new:request');
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
    protected function response()
    {
        $benchmark = $this->benchmark('new:response');
        try {
            $response = parent::response();
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
}
