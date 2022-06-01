<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Http;
use Spiral\App\TestApp;

abstract class HttpTest extends BaseTest
{
    /** @var TestApp */
    protected $app;

    /** @var Http */
    protected $http;

    public function setUp(): void
    {
        $this->app = $this->makeApp();
        $this->http = $this->app->get(Http::class);
    }

    protected function get(
        $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $this->http->handle($this->request($uri, 'GET', $query, $headers, $cookies));
    }

    protected function getWithAttributes(
        $uri,
        array $attributes,
        array $headers = []
    ): ResponseInterface {
        $r = $this->request($uri, 'GET', [], $headers, []);
        foreach ($attributes as $k => $v) {
            $r = $r->withAttribute($k, $v);
        }

        return $this->http->handle($r);
    }


    protected function post(
        $uri,
        array $data = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $this->http->handle(
            $this->request($uri, 'POST', [], $headers, $cookies)->withParsedBody($data)
        );
    }

    protected function request(
        $uri,
        string $method,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ServerRequest {
        $request = new ServerRequest(
            $method,
            $uri,
            $headers,
            'php://input'
        );

        return $request
            ->withQueryParams($query)
            ->withCookieParams($cookies);
    }

    protected function fetchCookies(array $header)
    {
        $result = [];
        foreach ($header as $line) {
            $cookie = explode('=', $line);
            $result[$cookie[0]] = rawurldecode(substr($cookie[1], 0, strpos($cookie[1], ';')));
        }

        return $result;
    }
}
