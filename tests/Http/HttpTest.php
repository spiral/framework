<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Psr\Http\Message\ResponseInterface;
use Spiral\Tests\BaseTest;
use Zend\Diactoros\ServerRequest;

abstract class HttpTest extends BaseTest
{
    protected function get(
        $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $this->http->perform($this->request($uri, $query, $headers, $cookies));
    }

    protected function post(
        $uri,
        array $data = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $this->http->perform(
            $this->request($uri, [], $headers, $cookies)->withParsedBody($data)
        );
    }

    protected function request(
        $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ServerRequest {
        return new ServerRequest(
            [],
            [],
            $uri,
            'GET',
            'php://input',
            $headers, $cookies,
            $query
        );

    }
}