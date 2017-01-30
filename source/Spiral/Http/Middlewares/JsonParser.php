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
 * set. Check alternative from ps7-middlewares to find alternative solution with more format
 * options.
 *
 * Incoming JSON parsed into array!
 */
class JsonParser implements MiddlewareInterface
{
    /**
     * @var bool
     */
    private $asArray;

    /**
     * @param bool $asArray
     */
    public function __construct(bool $asArray = true)
    {
        $this->asArray = $asArray;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        if (strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            try {
                $data = json_decode($request->getBody()->__toString(), $this->asArray);
                if ($data === null) {
                    //Mailformed request
                    return $response->withStatus(400);
                }

                $request = $request->withParsedBody($data);
            } catch (\Throwable $e) {
                //Mailformed request
                return $response->withStatus(400);
            }
        }

        return $next($request);
    }
}