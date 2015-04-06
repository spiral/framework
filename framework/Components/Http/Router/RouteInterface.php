<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Router;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Core;

interface RouteInterface
{
    /**
     * Get route name. Name is requires to correctly identify route inside router stack (to generate
     * url for example).
     *
     * @return string
     */
    public function getName();

    /**
     * Check if route matched with provided request. Will check url pattern and pre-conditions.
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function match(ServerRequestInterface $request);

    /**
     * Perform route on given Request and return response.
     *
     * @param ServerRequestInterface $request
     * @param Core                   $core
     * @param array                  $routeMiddlewares Middleware aliases provided from parent router.
     * @return mixed
     */
    public function perform(
        ServerRequestInterface $request,
        Core $core,
        array $routeMiddlewares = array()
    );

    /**
     * Create URL using route parameters (will be merged with default values), route pattern and base
     * path.
     *
     * @param array  $parameters
     * @param string $basePath
     * @return string
     */
    public function buildURL(array $parameters = array(), $basePath = '/');
}