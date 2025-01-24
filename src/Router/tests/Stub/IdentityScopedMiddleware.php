<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Stub;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Scope;
use Spiral\Core\ScopeInterface;

class IdentityScopedMiddleware implements MiddlewareInterface
{
    public function __construct(
        #[Proxy] private ScopeInterface $scope,
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        return $this->scope->runScope(
            new Scope(name: 'idenity', bindings: ['identity' => 'test-identity']),
            static fn() => $handler->handle($request),
        );
    }
}
