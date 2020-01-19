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
use Spiral\Config\JsonPayloadConfig;
use Spiral\Http\Exception\ClientException;

/**
 * Automatically parse application/json payloads.
 */
final class JsonPayloadMiddleware implements MiddlewareInterface
{
    /** @var JsonPayloadConfig */
    protected $config;

    /**
     * JsonPayloadMiddleware constructor.
     *
     * @param JsonPayloadConfig $config
     */
    public function __construct(JsonPayloadConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isJsonPayload($request)) {
            $request = $request->withParsedBody(json_decode((string)$request->getBody(), true));
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

        foreach ($this->config->getContentTypes() as $allowedType) {
            if (stripos($contentType, $allowedType) === 0) {
                return true;
            }
        }

        return false;
    }
}
