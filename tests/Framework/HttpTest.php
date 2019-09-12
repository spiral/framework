<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use Psr\Http\Message\ResponseInterface;
use Spiral\App\TestApp;
use Spiral\Http\Http;
use Zend\Diactoros\ServerRequest;

abstract class HttpTest extends BaseTest
{
    /** @var TestApp */
    protected $app;

    /** @var Http */
    protected $http;

    public function setUp()
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
