<?php

declare(strict_types=1);

namespace Spiral\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Bootloader\Http\JsonPayloadConfig;
use Spiral\Http\Exception\ClientException;

/**
 * Automatically parse application/json payloads.
 */
final class JsonPayloadMiddleware implements MiddlewareInterface
{
    /**
     * JsonPayloadMiddleware constructor.
     */
    public function __construct(
        private readonly JsonPayloadConfig $config
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isJsonPayload($request)) {
            $body = (string)$request->getBody();
            if ($body !== '') {
                $request = $request->withParsedBody(\json_decode($body, true));
                if (\json_last_error() !== 0) {
                    throw new ClientException(400, 'invalid json payload');
                }
            }
        }

        return $handler->handle($request);
    }

    private function isJsonPayload(ServerRequestInterface $request): bool
    {
        $contentType = $request->getHeaderLine('Content-Type');

        foreach ($this->config->getContentTypes() as $allowedType) {
            if (\stripos($contentType, $allowedType) === 0) {
                return true;
            }
        }

        return false;
    }
}
