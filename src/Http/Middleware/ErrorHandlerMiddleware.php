<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Spiral\Exceptions\HtmlHandler;
use Spiral\Exceptions\JsonHandler;
use Spiral\Http\ErrorHandler\RendererInterface;
use Spiral\Http\Exception\ClientException;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Router\Exception\RouterException;
use Spiral\Snapshots\SnapshotterInterface;

/**
 * Wraps Client and Routing exceptions into proper response.
 */
final class ErrorHandlerMiddleware implements MiddlewareInterface
{
    use LoggerTrait;

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /** @var bool */
    private $suppressErrors;

    /** @var RendererInterface */
    private $renderer;

    /** @var SnapshotterInterface|null */
    private $snapshots;

    /**
     * @param bool                      $suppressErrors
     * @param RendererInterface         $renderer
     * @param ResponseFactoryInterface  $responseFactory
     * @param SnapshotterInterface|null $snapshots
     */
    public function __construct(
        bool $suppressErrors,
        RendererInterface $renderer,
        ResponseFactoryInterface $responseFactory,
        SnapshotterInterface $snapshots = null
    ) {
        $this->suppressErrors = $suppressErrors;
        $this->renderer = $renderer;
        $this->responseFactory = $responseFactory;
        $this->snapshots = $snapshots;
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
        } catch (ClientException | RouterException $e) {
            if ($e instanceof ClientException) {
                $code = $e->getCode();
            } else {
                $code = 404;
            }
        } catch (\Throwable $e) {
            if ($this->snapshots !== null) {
                $this->snapshots->register($e);
            }

            $code = 500;

            if (!$this->suppressErrors) {
                return $this->renderException($request, $e);
            }
        }

        $this->logError($request, $code, $e->getMessage());

        return $this->renderer->renderException($request, $code, $e->getMessage());
    }

    /**
     * @param Request    $request
     * @param \Throwable $e
     * @return Response
     *
     * @throws \Throwable
     */
    private function renderException(Request $request, \Throwable $e): Response
    {
        $response = $this->responseFactory->createResponse(500);

        if ($request->getHeaderLine('Accept') == 'application/json') {
            $response = $response->withHeader('Content-Type', 'application/json');
            $handler = new JsonHandler();
            $response->getBody()->write(json_encode(
                ['status' => 500]
                + json_decode(
                    $handler->renderException($e, HtmlHandler::VERBOSITY_VERBOSE),
                    true
                )
            ));
        } else {
            $handler = new HtmlHandler();
            $response->getBody()->write($handler->renderException($e, HtmlHandler::VERBOSITY_VERBOSE));
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param int     $code
     * @param string  $message
     */
    private function logError(Request $request, int $code, string $message): void
    {
        $this->getLogger()->error(sprintf(
            '%s://%s%s caused the error %s (%s) by client %s.',
            $request->getUri()->getScheme(),
            $request->getUri()->getHost(),
            $request->getUri()->getPath(),
            $code,
            $message ?: '-not specified-',
            $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1'
        ));
    }
}
