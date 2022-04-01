<?php

declare(strict_types=1);

namespace Spiral\Http\Diactoros;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Config\HttpConfig;
use Laminas\Diactoros\Response;

final class ResponseFactory implements ResponseFactoryInterface
{
    public function __construct(
        private readonly HttpConfig $config
    ) {
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = new Response('php://memory', $code, []);
        $response = $response->withStatus($code, $reasonPhrase);

        foreach ($this->config->getBaseHeaders() as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        return $response;
    }
}
