<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /**
     * @param UriHandler $uriHandler
     * @return RouteInterface
     */
    public function withUriHandler(UriHandler $uriHandler): RouteInterface;

    /**
     * @return UriHandler
     */
    public function getUriHandler(): UriHandler;

    /**
     * Attach specific list of HTTP verbs to the route.
     *
     * @param string ...$verbs
     * @return RouteInterface|$this
     *
     * @throws RouteException
     */
    public function withVerbs(string ...$verbs): RouteInterface;

    /**
     * Return list of HTTP verbs route must handle.
     *
     * @return array
     */
    public function getVerbs(): array;

    /**
     * Returns new route instance with forced default values.
     *
     * @param array $defaults
     * @return RouteInterface|$this
     */
    public function withDefaults(array $defaults): RouteInterface;

    /**
     * Get default route values.
     *
     * @return array
     */
    public function getDefaults(): array;

    /**
     * Match route against given request, must return matched route instance or return null if
     * route does not match.
     *
     * @param Request $request
     * @return RouteInterface|$this|null
     *
     * @throws RouteException
     */
    public function match(Request $request): ?RouteInterface;

    /**
     * Return matched route parameters if any (must be populated by match call).
     *
     * @return array|null
     */
    public function getMatches(): ?array;

    /**
     * Generate valid route URL using set of routing parameters.
     *
     * @param array|\Traversable $parameters
     * @return UriInterface
     *
     * @throws RouteException
     */
    public function uri($parameters = []): UriInterface;
}
