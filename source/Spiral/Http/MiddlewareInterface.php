<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Common interface for spiral middlewares.
 */
interface MiddlewareInterface
{
    /**
     * Pass request thought middleware and receive resulted response.
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next Next middleware/target. Always returns ResponseInterface.
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next);
}