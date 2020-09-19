<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * @requires function \Spiral\Framework\Kernel::init
 */
class IntegrationTest extends TestCase
{
    private $app;

    public function setUp(): void
    {
        parent::setUp();
        $this->app = $this->makeApp(['DEBUG' => true]);
    }

    public function testRoute(): void
    {
        $r = $this->get('/');
        $this->assertStringContainsString('index', $r->getBody()->__toString());
    }

    public function testRoute2(): void
    {
        $r = $this->post('/');
        $this->assertStringContainsString('method', $r->getBody()->__toString());
    }

    public function get(
        $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $this->app->getHttp()->handle($this->request($uri, 'GET', $query, $headers, $cookies));
    }

    public function getWithAttributes(
        $uri,
        array $attributes,
        array $headers = []
    ): ResponseInterface {
        $r = $this->request($uri, 'GET', [], $headers, []);
        foreach ($attributes as $k => $v) {
            $r = $r->withAttribute($k, $v);
        }

        return $this->app->getHttp()->handle($r);
    }

    public function post(
        $uri,
        array $data = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $this->app->getHttp()->handle(
            $this->request($uri, 'POST', [], $headers, $cookies)->withParsedBody($data)
        );
    }

    public function request(
        $uri,
        string $method,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ServerRequest {
        $headers = array_merge([
            'accept-language' => 'en'
        ], $headers);

        return new ServerRequest(
            [],
            [],
            $uri,
            $method,
            'php://input',
            $headers,
            $cookies,
            $query
        );
    }

    public function fetchCookies(array $header)
    {
        $result = [];
        foreach ($header as $line) {
            $cookie = explode('=', $line);
            $result[$cookie[0]] = rawurldecode(substr($cookie[1], 0, strpos($cookie[1], ';')));
        }

        return $result;
    }
}
