<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\Core\Scope;
use Spiral\Core\ScopeInterface;
use Spiral\Framework\ScopeName;
use Spiral\Http\Exception\ClientException;
use Spiral\Http\Exception\ClientException\BadRequestException;
use Spiral\Http\Exception\ClientException\ForbiddenException;
use Spiral\Http\Exception\ClientException\NotFoundException;
use Spiral\Http\Exception\ClientException\UnauthorizedException;
use Spiral\Http\Stream\GeneratorStream;
use Spiral\Http\Traits\JsonTrait;
use Spiral\Router\Exception\HandlerException;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;

final class CoreHandler implements RequestHandlerInterface
{
    use JsonTrait;

    private readonly TracerInterface $tracer;

    /** @readonly */
    private ?string $controller = null;
    /** @readonly */
    private ?string $action = null;
    /** @readonly */
    private ?bool $verbActions = null;
    /** @readonly */
    private ?array $parameters = null;

    public function __construct(
        private readonly CoreInterface $core,
        private readonly ScopeInterface $scope,
        private readonly ResponseFactoryInterface $responseFactory,
        ?TracerInterface $tracer = null
    ) {
        $this->tracer = $tracer ?? new NullTracer($scope);
    }

    /**
     * @mutation-free
     */
    public function withContext(string $controller, string $action, array $parameters): CoreHandler
    {
        $handler = clone $this;
        $handler->controller = $controller;
        $handler->action = $action;
        $handler->parameters = $parameters;

        return $handler;
    }

    /**
     * Disable or enable HTTP prefix for actions.
     *
     * @mutation-free
     */
    public function withVerbActions(bool $verbActions): CoreHandler
    {
        $handler = clone $this;
        $handler->verbActions = $verbActions;

        return $handler;
    }

    /**
     * @psalm-suppress UnusedVariable
     * @throws \Throwable
     */
    public function handle(Request $request): Response
    {
        $this->checkValues();
        $controller = $this->controller;
        $parameters = $this->parameters;

        $outputLevel = \ob_get_level();
        \ob_start();

        $result = null;
        $output = '';

        $response = $this->responseFactory->createResponse(200);
        try {
            $action = $this->verbActions
                ? \strtolower($request->getMethod()) . \ucfirst($this->action)
                : $this->action;

            // run the core withing PSR-7 Request/Response scope
            /**
             * @psalm-suppress InvalidArgument
             * TODO: Can we bind all controller classes at the bootstrap stage?
             */
            $result = $this->scope->runScope(
                new Scope(
                    name: ScopeName::HttpRequest,
                    bindings: [Request::class => $request, Response::class => $response, $controller => $controller],
                ),
                fn (): mixed => $this->tracer->trace(
                    name: 'Controller [' . $controller . ':' . $action . ']',
                    callback: fn (): mixed => $this->core->callAction(
                        controller: $controller,
                        action: $action,
                        parameters: $parameters,
                    ),
                    attributes: [
                        'route.controller' => $this->controller,
                        'route.action' => $action,
                        'route.parameters' => \array_keys($parameters),
                    ]
                )
            );
        } catch (ControllerException $e) {
            \ob_get_clean();
            throw $this->mapException($e);
        } catch (\Throwable $e) {
            \ob_get_clean();
            throw $e;
        } finally {
            while (\ob_get_level() > $outputLevel + 1) {
                $output = \ob_get_clean() . $output;
            }
        }

        return $this->wrapResponse(
            $response,
            $result,
            \ob_get_clean() . $output,
        );
    }

    /**
     * Convert endpoint result into valid response.
     *
     * @param Response $response Initial pipeline response.
     * @param mixed    $result   Generated endpoint output.
     * @param string   $output   Buffer output.
     */
    private function wrapResponse(Response $response, mixed $result = null, string $output = ''): Response
    {
        if ($result instanceof Response) {
            if ($output !== '' && $result->getBody()->isWritable()) {
                $result->getBody()->write($output);
            }

            return $result;
        }

        if ($result instanceof \Generator) {
            return $response->withBody(new GeneratorStream($result));
        }

        if (\is_array($result) || $result instanceof \JsonSerializable) {
            $response = $this->writeJson($response, $result);
        } else {
            $response->getBody()->write((string)$result);
        }

        //Always glue buffered output
        $response->getBody()->write($output);

        return $response;
    }

    /**
     * Converts core specific ControllerException into HTTP ClientException.
     */
    private function mapException(ControllerException $exception): ClientException
    {
        return match ($exception->getCode()) {
            ControllerException::BAD_ACTION,
            ControllerException::NOT_FOUND => new NotFoundException('Not found', $exception),
            ControllerException::FORBIDDEN => new ForbiddenException('Forbidden', $exception),
            ControllerException::UNAUTHORIZED => new UnauthorizedException('Unauthorized', $exception),
            default => new BadRequestException('Bad request', $exception),
        };
    }

    /**
     * @psalm-assert !null $this->controller
     * @psalm-assert !null $this->action
     * @psalm-assert !null $this->parameters
     * @mutation-free
     */
    private function checkValues(): void
    {
        if ($this->controller === null) {
            throw new HandlerException('Controller and action pair are not set.');
        }
    }
}
