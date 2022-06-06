<?php

declare(strict_types=1);

namespace Spiral\Http\Response;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Config\HttpConfig;

final class ResponseFactory implements ResponseFactoryInterface
{
    public function __construct(
        private readonly HttpConfig $config,
        private readonly Psr17Factory $factory
    ) {
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = $this->factory->createResponse($code, $reasonPhrase);
        foreach ($this->config->getBaseHeaders() as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        return $response;
    }
}
