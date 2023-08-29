<?php

declare(strict_types=1);

namespace Spiral\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\Exceptions\ExceptionHandlerInterface;
use Spiral\Exceptions\ExceptionRendererInterface;
use Spiral\Exceptions\Verbosity;
use Spiral\Http\ErrorHandler\RendererInterface;
use Spiral\Http\Exception\ClientException;
use Spiral\Http\Header\AcceptHeader;
use Spiral\Http\Middleware\ErrorHandlerMiddleware\SuppressErrorsInterface;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Router\Exception\RouterException;

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
        private readonly ExceptionHandlerInterface $errorHandler,
        private readonly Verbosity $verbosity = Verbosity::VERBOSE,
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
        } catch (ClientException|RouterException $e) {
            $code = $e instanceof ClientException ? $e->getCode() : 404;
        } catch (\Throwable $e) {
            $code = 500;
        }

        $this->errorHandler->report($e);

        if (!$this->suppressErrors->suppressed()) {
            return $this->renderError($request, $e, $code);
        }

        $this->logError($request, $code, $e->getMessage());

        return $this->renderer->renderException($request, $code, $e);
    }

    /**
     * @throws \Throwable
     */
    private function renderError(Request $request, \Throwable $e, int $code): Response
    {
        $response = $this->responseFactory->createResponse($code);

        [$format, $renderer] = $this->getRenderer($this->errorHandler, $request);

        if ($format !== null) {
            $response = $response->withHeader('Content-Type', $format . '; charset=UTF-8');
        }

        $response->getBody()->write(
            (string)$renderer?->render(
                exception: $e,
                verbosity: $this->verbosity,
                format: $format
            )
        );

        return $response;
    }

    /**
     * @return array{string|null, ExceptionRendererInterface|null}
     */
    private function getRenderer(ExceptionHandlerInterface $handler, Request $request): array
    {
        if ($request->hasHeader('Accept')) {
            $acceptItems = AcceptHeader::fromString($request->getHeaderLine('Accept'))->getAll();
            foreach ($acceptItems as $item) {
                $format = $item->getValue();
                $renderer = $handler->getRenderer($format);
                if ($renderer !== null) {
                    return [$format, $renderer];
                }
            }
        }
        return [null, $handler->getRenderer()];
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
}
