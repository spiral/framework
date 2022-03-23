<?php

declare(strict_types=1);

namespace Spiral\Http\Middleware;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Spiral\Debug\StateInterface;
use Spiral\Exceptions\HtmlHandler;
use Spiral\Exceptions\JsonHandler;
use Spiral\Http\ErrorHandler\RendererInterface;
use Spiral\Http\Exception\ClientException;
use Spiral\Http\Middleware\ErrorHandlerMiddleware\SuppressErrorsInterface;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Router\Exception\RouterException;
use Spiral\Snapshots\SnapshotterInterface;

/**
 * Wraps Client and Routing exceptions into proper response.
 */
final class ErrorHandlerMiddleware implements MiddlewareInterface
{
    use LoggerTrait;

    public function __construct(
        private readonly SuppressErrorsInterface $suppressErrors,
        private readonly RendererInterface $renderer,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ContainerInterface $container
    ) {
    }

    /**
     * @psalm-suppress UnusedVariable
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
            $snapshotter = $this->getOptional(SnapshotterInterface::class);
            if ($snapshotter !== null) {
                /** @var SnapshotterInterface $snapshotter */
                $snapshotter->register($e);
            }

            if (!$this->suppressErrors->suppressed()) {
                return $this->renderError($request, $e);
            }

            $code = 500;
        }

        $this->logError($request, $code, $e->getMessage());

        return $this->renderer->renderException($request, $code, $e->getMessage());
    }

    /**
     * @throws \Throwable
     */
    private function renderError(Request $request, \Throwable $e): Response
    {
        $response = $this->responseFactory->createResponse(500);

        if ($request->getHeaderLine('Accept') === 'application/json') {
            $response = $response->withHeader('Content-Type', 'application/json');
            $handler = new JsonHandler();
            $response->getBody()->write(
                \json_encode(
                    ['status' => 500]
                    + \json_decode(
                        $handler->renderException($e, JsonHandler::VERBOSITY_VERBOSE),
                        true
                    )
                )
            );
        } else {
            $handler = new HtmlHandler();
            $state = $this->getOptional(StateInterface::class);
            if ($state !== null) {
                $handler = $handler->withState($state);
            }

            $response->getBody()->write($handler->renderException($e, HtmlHandler::VERBOSITY_VERBOSE));
        }

        return $response;
    }

    private function logError(Request $request, int $code, string $message): void
    {
        $this->getLogger()->error(
            \sprintf(
                '%s://%s%s caused the error %s (%s) by client %s.',
                $request->getUri()->getScheme(),
                $request->getUri()->getHost(),
                $request->getUri()->getPath(),
                $code,
                $message ?: '-not specified-',
                $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1'
            )
        );
    }

    private function getOptional(string $class): mixed
    {
        try {
            return $this->container->get($class);
        } catch (\Throwable | ContainerExceptionInterface) {
            return null;
        }
    }
}
