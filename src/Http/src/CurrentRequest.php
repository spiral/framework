<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Attribute\Scope;
use Spiral\Http\Exception\HttpException;

/**
 * Provides access to the current request in HTTP scope.
 */
#[Scope('http')]
final class CurrentRequest
{
    private ?ServerRequestInterface $request = null;

    public function set(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function get(): ServerRequestInterface
    {
        return $this->request ?? throw new HttpException('Unable to resolve current request.');
    }
}
