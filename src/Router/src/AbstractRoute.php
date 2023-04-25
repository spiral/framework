<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Spiral\Router\Traits\DefaultsTrait;
use Spiral\Router\Traits\VerbsTrait;

/**
 * @psalm-import-type Matches from UriHandler
 */
abstract class AbstractRoute implements RouteInterface
{
    use VerbsTrait;
    use DefaultsTrait;

    /**
     * @readonly
     */
    protected UriHandler $uriHandler;

    /**
     * @var null|Matches
     * @readonly
     */
    protected ?array $matches = null;

    public function __construct(
        protected string $pattern,
        array $defaults = []
    ) {
        $this->defaults = $defaults;
    }

    /**
     * @mutation-free
     */
    public function withUriHandler(UriHandler $uriHandler): static
    {
        $route = clone $this;
        $route->uriHandler = $uriHandler->withPattern($this->pattern);

        return $route;
    }

    public function getUriHandler(): UriHandler
    {
        return $this->uriHandler;
    }

    /**
     * @mutation-free
     */
    public function match(Request $request): ?static
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
