<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Spiral\Router\Traits\DefaultsTrait;
use Spiral\Router\Traits\VerbsTrait;

abstract class AbstractRoute implements RouteInterface
{
    use VerbsTrait;
    use DefaultsTrait;

    protected UriHandler $uriHandler;
    protected ?array $matches = null;

    public function __construct(
        protected string $pattern,
        array $defaults = []
    ) {
        $this->defaults = $defaults;
    }

    public function withUriHandler(UriHandler $uriHandler): RouteInterface
    {
        $route = clone $this;
        $route->uriHandler = $uriHandler->withPattern($this->pattern);

        return $route;
    }

    public function getUriHandler(): UriHandler
    {
        return $this->uriHandler;
    }

    public function match(Request $request): ?RouteInterface
    {
        if (!\in_array(\strtoupper($request->getMethod()), $this->getVerbs(), true)) {
            return null;
        }

        $matches = $this->uriHandler->match($request->getUri(), $this->defaults);
        if ($matches === null) {
            return null;
        }

        $route = clone $this;
        $route->matches = $matches;

        return $route;
    }

    public function getMatches(): ?array
    {
        return $this->matches;
    }

    public function uri(iterable $parameters = []): UriInterface
    {
        return $this->uriHandler->uri(
            $parameters,
            \array_merge($this->defaults, $this->matches ?? [])
        );
    }
}
