<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Http\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Spiral\Http\Errors\RendererInterface;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Router\Exceptions\RouteNotFoundException;

/**
 * Wraps Client and Routing exceptions into proper response.
 */
class ExceptionsMiddleware implements MiddlewareInterface
{
    use LoggerTrait;

    /** @var RendererInterface */
    private $renderer;

    /**
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @inheritdoc
     */
    public function process(Request $request, Handler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (ClientException|RouteNotFoundException $e) {
            $code = $e instanceof ClientException ? $e->getCode() : 404;
            $this->logError($request, $code, $e->getMessage());

            return $this->renderer->renderException($request, $code, $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param int     $code
     * @param string  $message
     */
    private function logError(Request $request, int $code, string $message)
    {
        $this->getLogger()->error(sprintf(
            "%s://%s%s caused the error %s (%s) by client %s.",
            $request->getUri()->getScheme(),
            $request->getUri()->getHost(),
            $request->getUri()->getPath(),
            $code,
            $message ?: '-not specified-',
            $this->getIP($request)
        ));
    }

    /**
     * Try to locate client ip. For debug purposes only!
     *
     * @param Request $request
     * @return string
     */
    private function getIP(Request $request): string
    {
        return $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}