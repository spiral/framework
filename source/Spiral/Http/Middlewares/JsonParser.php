<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Http\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Http\MiddlewareInterface;

/**
 * Populates parsedBody data of request with decoded json content if appropriate request header
 * set.
 */
class JsonParser implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        if (strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            try {
                $data = json_decode($request->getBody()->__toString(), true);
                $request = $request->withParsedBody($data);
            } catch (\ErrorException $e) {
                //Mailformed request
                return $response->withStatus(400);
            }
        }

        return $next($request);
    }
}