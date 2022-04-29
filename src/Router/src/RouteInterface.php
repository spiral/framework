<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Router\Exception\RouteException;

/**
 * Route provides ability to handle incoming request based on defined pattern. Each route must be
 * isolated using given container.
 */
interface RouteInterface extends RequestHandlerInterface
{
    /**
     * List of possible verbs for the route.
     */
    public const VERBS = ['GET', 'POST', 'PUT', 'PATCH', 'OPTIONS', 'HEAD', 'DELETE'];

    public function withUriHandler(UriHandler $uriHandler): RouteInterface;

    public function getUriHandler(): UriHandler;

    /**
     * Attach specific list of HTTP verbs to the route.
     *
     * @throws RouteException
     */
    public function withVerbs(string ...$verbs): RouteInterface;

    /**
     * Return list of HTTP verbs route must handle.
     */
    public function getVerbs(): array;

    /**
     * Returns new route instance with forced default values.
     */
    public function withDefaults(array $defaults): RouteInterface;

    /**
     * Get default route values.
     */
    public function getDefaults(): array;

    /**
     * Match route against given request, must return matched route instance or return null if
     * route does not match.
     *
     * @return RouteInterface|$this|null
     * @throws RouteException
     */
    public function match(Request $request): ?RouteInterface;

    /**
     * Return matched route parameters if any (must be populated by match call).
     */
    public function getMatches(): ?array;

    /**
     * Generate valid route URL using set of routing parameters.
     *
     * @throws RouteException
     */
    public function uri(iterable $parameters = []): UriInterface;
}
