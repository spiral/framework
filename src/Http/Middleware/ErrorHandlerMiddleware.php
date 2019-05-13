<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Http\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Spiral\Http\ErrorHandler\RendererInterface;
use Spiral\Http\Exception\ClientException;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Router\Exception\RouteNotFoundException;
use Spiral\Snapshots\SnapshotterInterface;

/**
 * Wraps Client and Routing exceptions into proper response.
 */
class ErrorHandlerMiddleware implements MiddlewareInterface
{
    use LoggerTrait;

    /** @var bool */
    private $suppressErrors;

    /** @var RendererInterface */
    private $renderer;

    /** @var SnapshotterInterface|null */
    private $snapshotter;

    /**
     * @param bool                      $suppressErrors
     * @param RendererInterface         $renderer
     * @param SnapshotterInterface|null $snapshots
     */
    public function __construct(
        bool $suppressErrors,
        RendererInterface $renderer,
        SnapshotterInterface $snapshots = null
    ) {
        $this->suppressErrors = $suppressErrors;
        $this->renderer = $renderer;
        $this->snapshotter = $snapshots;
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
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

            if ($this->snapshotter !== null) {
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
            $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1'
        ));
    }
}