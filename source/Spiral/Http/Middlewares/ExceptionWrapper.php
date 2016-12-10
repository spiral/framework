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
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\ErrorWriter;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\MiddlewareInterface;

/**
 * Isolates exceptions into response. Ability to isolate regular exceptions is under re-thinking
 * now.
 *
 * Attention, middleware requests ViewsInterface on demand!
 */
class ExceptionWrapper extends Component implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerTrait;

    /**
     * Format to be used for log messages in cases where http error caused by client request.
     */
    const LOG_FORMAT = "{scheme}://{host}{path} caused the error {code} ({message}) by client {remote}.";

    /**
     * Contain list of error pages.
     *
     * @var HttpConfig
     */
    protected $httpConfig = null;

    /**
     * Required to get ErrorWriter on demand and fetch proper logger.
     *
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param HttpConfig         $httpConfig
     * @param ContainerInterface $container
     */
    public function __construct(HttpConfig $httpConfig, ContainerInterface $container)
    {
        $this->httpConfig = $httpConfig;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $outputLevel = ob_get_level();

        try {
            /**
             * Debug: exceptions and clientExceptions are isolated in this middleware.
             */
            return $next($request, $response);
        } catch (ClientException $exception) {
            while (ob_get_level() > $outputLevel) {
                //Flushing all unclosed buffers
                ob_end_clean();
            }

            //Logging client error
            $this->logError($request, $exception);

            //Writing nice error into response
            return $this->errorWriter()->writeException($request, $response, $exception);
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
