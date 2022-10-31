<?php

declare(strict_types=1);

namespace Spiral\Auth\Middleware\Firewall;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RedirectFirewall extends AbstractFirewall
{
    public function __construct(
        protected readonly UriInterface $uri,
        protected readonly ResponseFactoryInterface $responseFactory,
        protected readonly int $status = 302
    ) {
    }

    protected function denyAccess(Request $request, RequestHandlerInterface $handler): Response
    {
        return $this->responseFactory->createResponse($this->status)->withHeader('Location', (string) $this->uri);
    }
}
