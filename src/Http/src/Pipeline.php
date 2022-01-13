<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Http\Exception\PipelineException;
use Spiral\Http\Traits\MiddlewareTrait;

/**
 * Pipeline used to pass request and response thought the chain of middleware.
 */
final class Pipeline implements RequestHandlerInterface, MiddlewareInterface
{
    use MiddlewareTrait;

    /** @var ScopeInterface */
    private $scope;

    /** @var int */
    private $position = 0;

    /** @var RequestHandlerInterface */
    private $handler;

    public function __construct(ScopeInterface $scope)
    {
        $this->scope = $scope;
    }

    /**
     * Configures pipeline with target endpoint.
     *
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

    /**
     * @inheritdoc
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        return $this->withHandler($handler)->handle($request);
    }

    /**
     * @inheritdoc
     */
    public function handle(Request $request): Response
    {
        if (empty($this->handler)) {
            throw new PipelineException('Unable to run pipeline, no handler given.');
        }

        $position = $this->position++;
        if (isset($this->middleware[$position])) {
            return $this->middleware[$position]->process($request, $this);
        }

        return $this->scope->runScope([Request::class => $request], function () use ($request) {
            return $this->handler->handle($request);
        });
    }
}
