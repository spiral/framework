<?php

declare(strict_types=1);

namespace Spiral\Csrf\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Requires CSRF token to presented in every passed request (no matter request method).
 */
final class StrictCsrfFirewall implements MiddlewareInterface
{
    private readonly CsrfFirewall $csrfFirewall;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->csrfFirewall = new CsrfFirewall($responseFactory, []);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->csrfFirewall->process($request, $handler);
    }
}
