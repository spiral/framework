<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerAwareInterface;
use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Debug\SnapshotInterface;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Exceptions\ClientExceptions\ServerErrorException;
use Spiral\Http\MiddlewareInterface;
use Spiral\Http\Traits\JsonTrait;
use Spiral\Views\ViewsInterface;

/**
 * Isolates exceptions into response.
 */
class ExceptionIsolator extends Component implements MiddlewareInterface, LoggerAwareInterface
{
    /**
     * To write json responses.
     */
    use JsonTrait, LoggerTrait;

    /**
     * Format to be used for log messages in cases where http error caused by client request.
     */
    const LOG_FORMAT = "{scheme}://{host}{path} caused the error {code} ({message}) by client {remote}.";

    /**
     * Contain list of error pages.
     *
     * @var HttpConfig
     */
    protected $config = null;

    /**
     * Required to render error pages via ViewsInterface.
     *
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param HttpConfig         $config
     * @param ContainerInterface $container
     */
    public function __construct(HttpConfig $config, ContainerInterface $container)
    {
        $this->config = $config;
        $this->container = $container;
    }

    /**
     * Write ClientException content into response.
     *
     * @param Request         $request
     * @param Response        $response
     * @param ClientException $exception
     * @return Request
     */
    public function writeException(Request $request, Response $response, ClientException $exception)
    {
        //Has to contain valid http code
        $response = $response->withStatus($exception->getCode());

        if ($request->getHeaderLine('Accept') == 'application/json') {
            //Json got requested
            return $this->writeJson($response, ['status' => $exception->getCode()]);
        }

        if (
            !$this->config->hasView($exception->getCode())
            || !$this->container->has(ViewsInterface::class)
        ) {
            //We don't or can't render http error view
            return $response;
        }

        /**
         * @var ViewsInterface $views
         */
        $views = $this->container->get(ViewsInterface::class);

        $errorPage = $views->render($this->config->errorView($exception->getCode()), [
            'httpConfig' => $this->config,
            'request'    => $request
        ]);

        $response->getBody()->write($errorPage);

        return $response;
    }

    /**
     * Write snapshot content into exception.
     *
     * @param Request           $request
     * @param Response          $response
     * @param SnapshotInterface $snapshot
     * @return Response
     */
    public function writeSnapshot(Request $request, Response $response, SnapshotInterface $snapshot)
    {
        //Exposing exception
        if ($request->getHeaderLine('Accept') != 'application/json') {
            $response->getBody()->write($snapshot->render());

            //Normal exception page
            return $response->withStatus(ClientException::ERROR);
        }

        //Exception in a form of JSON object
        return $this->writeJson($response, $snapshot->describe(), ClientException::ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        try {
            /**
             * Debug: exceptions and clientExceptions are isolated in this middleware.
             */
            return $next($request, $response);

        } catch (ClientException $exception) {

            //Logging client error
            $this->logError($request, $exception);

            return $this->writeException($request, $response, $exception);
        } catch (\Exception $exception) {
            /**
             * Potentially has to be dedicated to specific service.
             *
             * @var SnapshotInterface $snapshot
             */
            $snapshot = $this->container->construct(SnapshotInterface::class, compact('exception'));

            //Snapshot must report about itself
            $snapshot->report();

            if (!$this->config->exposeErrors()) {
                //We are not allowed to share snapshots
                return $this->writeException($request, $response, new ServerErrorException());
            }

            return $this->writeSnapshot($request, $response, $snapshot);
        }
    }

    /**
     * Add error to error log.
     *
     * @param Request         $request
     * @param ClientException $exception
     */
    private function logError(Request $request, ClientException $exception)
    {
        $remoteAddress = '-undefined-';
        if (!empty($request->getServerParams()['REMOTE_ADDR'])) {
            $remoteAddress = $request->getServerParams()['REMOTE_ADDR'];
        }

        $this->logger()->error(\Spiral\interpolate(static::LOG_FORMAT, [
            'scheme'  => $request->getUri()->getScheme(),
            'host'    => $request->getUri()->getHost(),
            'path'    => $request->getUri()->getPath(),
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage() ?: '-not specified-',
            'remote'  => $remoteAddress
        ]));
    }
}