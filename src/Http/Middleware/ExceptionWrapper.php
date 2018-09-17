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
use Spiral\Snapshots\SnapshotterInterface;

/**
 * Wraps Client and Routing exceptions into proper response.
 */
class ExceptionWrapper implements MiddlewareInterface
{
    use LoggerTrait;

    /** @var RendererInterface */
    private $renderer;

    /** @var bool */
    private $suppressErrors;

    /** @var SnapshotterInterface|null */
    private $snapshotter;

    /**
     * @param RendererInterface         $renderer
     * @param bool                      $suppressErrors
     * @param SnapshotterInterface|null $snapshotter
     */
    public function __construct(
        RendererInterface $renderer,
        bool $suppressErrors = true,
        SnapshotterInterface $snapshotter = null
    ) {
        $this->renderer = $renderer;
        $this->suppressErrors = $suppressErrors;
        $this->snapshotter = $snapshotter;
    }

    /**
     * @inheritdoc
     */
    public function process(Request $request, Handler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (ClientException|RouteNotFoundException $e) {
            if ($e instanceof ClientException) {
                $code = $e->getCode();
            } else {
                $code = 404;
            }
        } catch (\Throwable $e) {
            if (!$this->suppressErrors) {
                throw $e;
            }

            if (!empty($this->snapshotter)) {
                $this->snapshotter->register($e);
            }

            $code = 500;
        }

        $this->logError($request, $code, $e->getMessage());

        return $this->renderer->renderException($request, $code, $e->getMessage());
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