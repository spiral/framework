<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Http\Event\MiddlewareProcessing;
use Spiral\Http\Exception\PipelineException;
use Spiral\Http\Traits\MiddlewareTrait;

/**
 * Pipeline used to pass request and response thought the chain of middleware.
 */
final class Pipeline implements RequestHandlerInterface, MiddlewareInterface
{
    use MiddlewareTrait;

    private int $position = 0;
    private ?RequestHandlerInterface $handler = null;

    public function __construct(
        private readonly ScopeInterface $scope,
        private readonly ?EventDispatcherInterface $dispatcher = null
    ) {
    }

    /**
     * Configures pipeline with target endpoint.
     *
     * @throws PipelineException
     */
    public function withHandler(RequestHandlerInterface $handler): self
    {
        $pipeline = clone $this;
        $pipeline->handler = $handler;
        $pipeline->position = 0;

        return $pipeline;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        return $this->withHandler($handler)->handle($request);
    }

    public function handle(Request $request): Response
    {
        if ($this->handler === null) {
            throw new PipelineException('Unable to run pipeline, no handler given.');
        }

        $position = $this->position++;
        if (isset($this->middleware[$position])) {
            $middleware = $this->middleware[$position];
            $this->dispatcher?->dispatch(new MiddlewareProcessing($request, $middleware));

            return $middleware->process($request, $this);
        }

        $handler = $this->handler;
        return $this->scope->runScope(
            [Request::class => $request],
            static fn (): Response => $handler->handle($request)
        );
    }
}
