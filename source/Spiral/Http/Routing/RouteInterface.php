<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Http\Exceptions\RouteException;

/**
 * Declares ability to route.
 */
interface RouteInterface
{
    /**
     * Isolate route endpoint in a given container.
     *
     * @param ContainerInterface $container
     *
     * @return self
     */
    public function withContainer(ContainerInterface $container): RouteInterface;

    /**
     * Returns new route instance.
     *
     * @param string $name
     *
     * @return RouteInterface
     */
    public function withName(string $name): RouteInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $prefix
     *
     * @return self
     */
    public function withPrefix(string $prefix): RouteInterface;

    /**
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Returns new route instance.
     *
     * @param array $matches
     *
     * @return self
     */
    public function withDefaults(array $matches): RouteInterface;

    /**
     * Get default route values.
     *
     * @return array
     */
    public function getDefaults(): array;

    /**
     * Check if route matched with provided request. Must return new route in case of success.
     *
     * @param ServerRequestInterface $request
     *
     * @return self|null
     *
     * @throws RouteException
     */
    public function match(ServerRequestInterface $request);

    /**
     * Execute route on given request. Has to be called after match method.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function perform(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface;

    /**
     * Generate valid route URL using route name and set of parameters.
     *
     * @param array|\Traversable $parameters
     *
     * @return UriInterface
     *
     * @throws RouteException
     */
    public function uri($parameters = []): UriInterface;
}
