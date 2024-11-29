<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Attribute\Scope;

/**
 * Provides access to the current request in the `http` scope.
 * @internal
 */
#[Scope('http')]
final class CurrentRequest
{
    private ?ServerRequestInterface $request = null;

    public function set(?ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function get(): ?ServerRequestInterface
    {
        return $this->request;
    }
}
