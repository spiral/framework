<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\Exception\ClientException;

/**
 * Automatically parse application/json payloads.
 */
final class JsonPayloadMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isJsonPayload($request)) {
            $request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));
            if (json_last_error() !== 0) {
                throw new ClientException(400, 'invalid json payload');
            }
        }

        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    private function isJsonPayload(ServerRequestInterface $request): bool
    {
        $contentType = $request->getHeaderLine('Content-Type');

        return stripos($contentType, 'application/json') === 0;
    }
}
