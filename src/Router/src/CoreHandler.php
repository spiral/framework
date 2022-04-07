<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Throwable;
use Generator;
use JsonSerializable;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\Core\ScopeInterface;
use Spiral\Http\Exception\ClientException;
use Spiral\Http\Exception\ClientException\BadRequestException;
use Spiral\Http\Exception\ClientException\ForbiddenException;
use Spiral\Http\Exception\ClientException\NotFoundException;
use Spiral\Http\Exception\ClientException\UnauthorizedException;
use Spiral\Http\Stream\GeneratorStream;
use Spiral\Http\Traits\JsonTrait;
use Spiral\Router\Exception\HandlerException;

final class CoreHandler implements RequestHandlerInterface
{
    use JsonTrait;

    private CoreInterface $core;

    private ScopeInterface $scope;

    private ?string $controller = null;

    private ?string $action = null;

    private ?bool $verbActions = null;

    private ?array $parameters = null;

    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        CoreInterface $core,
        ScopeInterface $scope,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->core = $core;
        $this->scope = $scope;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param string|null $action
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
     */
    public function withVerbActions(bool $verbActions): CoreHandler
    {
        $handler = clone $this;
        $handler->verbActions = $verbActions;

        return $handler;
    }

    /**
     * @inheritdoc
     *
     * @psalm-suppress UnusedVariable
     * @throws Throwable
     */
    public function handle(Request $request): Response
    {
        if ($this->controller === null) {
            throw new HandlerException('Controller and action pair is not set');
        }

        $outputLevel = ob_get_level();
        ob_start();

        $output = $result = null;

        $response = $this->responseFactory->createResponse(200);
        try {
            // run the core withing PSR-7 Request/Response scope
            $result = $this->scope->runScope(
                [
                    Request::class  => $request,
                    Response::class => $response,
                ],
                fn() => $this->core->callAction(
                    $this->controller,
                    $this->getAction($request),
                    $this->parameters
                )
            );
        } catch (ControllerException $e) {
            ob_get_clean();
            throw $this->mapException($e);
        } catch (Throwable $e) {
            ob_get_clean();
            throw $e;
        } finally {
            while (ob_get_level() > $outputLevel + 1) {
                $output = ob_get_clean() . $output;
            }
        }

        return $this->wrapResponse(
            $response,
            $result,
            ob_get_clean() . $output
        );
    }

    private function getAction(Request $request): string
    {
        if ($this->verbActions) {
            return strtolower($request->getMethod()) . ucfirst($this->action);
        }

        return $this->action;
    }

    /**
     * Convert endpoint result into valid response.
     *
     * @param Response $response Initial pipeline response.
     * @param mixed    $result   Generated endpoint output.
     * @param string   $output   Buffer output.
     */
    private function wrapResponse(Response $response, $result = null, string $output = ''): Response
    {
        if ($result instanceof Response) {
            if ($output !== '' && $result->getBody()->isWritable()) {
                $result->getBody()->write($output);
            }

            return $result;
        }

        if ($result instanceof Generator) {
            return $response->withBody(new GeneratorStream($result));
        }

        if (\is_array($result) || $result instanceof JsonSerializable) {
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
        switch ($exception->getCode()) {
            case ControllerException::BAD_ACTION:
            case ControllerException::NOT_FOUND:
                return new NotFoundException($exception->getMessage());
            case ControllerException::FORBIDDEN:
                return new ForbiddenException($exception->getMessage());
            case ControllerException::UNAUTHORIZED:
                return new UnauthorizedException($exception->getMessage());
            default:
                return new BadRequestException($exception->getMessage());
        }
    }
}
