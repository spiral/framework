<?php

declare(strict_types=1);

namespace Spiral\Http\Traits;

use Psr\Http\Message\ResponseInterface;

/**
 * Provides ability to write json payloads into responses.
 */
trait JsonTrait
{
    /**
     * Generate JSON response.
     */
    private function writeJson(ResponseInterface $response, mixed $payload, int $code = 200): ResponseInterface
    {
        if ($payload instanceof \JsonSerializable) {
            $payload = $payload->jsonSerialize();
        }

        if (\is_array($payload) && isset($payload['status']) && \is_int($payload['status'])) {
            $code = $payload['status'];
        }

        $response->getBody()->write(\json_encode($payload));

        return $response->withStatus($code)->withHeader('Content-Type', 'application/json');
    }
}
