<?php

declare(strict_types=1);

namespace Spiral\Tests\Cookies;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Config\HttpConfig;
use Nyholm\Psr7\Response;

final class TestResponseFactory implements ResponseFactoryInterface
{
    public function __construct(protected HttpConfig $config) {}

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = new Response($code);
        $response = $response->withStatus($code, $reasonPhrase);

        foreach ($this->config->getBaseHeaders() as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        return $response;
    }
}
