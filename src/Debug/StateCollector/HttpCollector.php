<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug\StateCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Debug\StateCollectorInterface;
use Spiral\Debug\StateInterface;

final class HttpCollector implements MiddlewareInterface, StateCollectorInterface
{
    /** @var ServerRequestInterface */
    private $request;

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->request = $request;
        return $handler->handle($request);
    }

    /**
     * @param StateInterface $state
     */
    public function populate(StateInterface $state): void
    {
        if (!$this->request === null) {
            return;
        }

        $state->setTag('method', $this->request->getMethod());
        $state->setTag('url', (string)$this->request->getUri());

        $state->setVariable('headers', $this->request->getHeaders());

        if ($this->request->getQueryParams() !== []) {
            $state->setVariable('query', $this->request->getQueryParams());
        }

        if ($this->request->getParsedBody() !== null) {
            $state->setVariable('data', $this->request->getParsedBody());
        }
    }

    /**
     * Reset captured request.
     */
    public function reset(): void
    {
        $this->request = null;
    }
}
