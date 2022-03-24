<?php

declare(strict_types=1);

namespace Spiral\Auth\Middleware\Firewall;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Changes the target URL to login form without altering URL in client browser. Changes response status.
 */
final class OverwriteFirewall extends AbstractFirewall
{
    public function __construct(
        private readonly UriInterface $uri,
        private readonly int $status = 401
    ) {
    }

    protected function denyAccess(Request $request, RequestHandlerInterface $handler): Response
    {
        return $handler->handle($request->withUri($this->uri))->withStatus($this->status);
    }
}
