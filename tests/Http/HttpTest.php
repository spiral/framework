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
        $request = new ServerRequest(
            [],
            [],
            $uri,
            'GET',
            'php://input',
            $headers, $cookies,
            $query
        );

        return $this->http->perform($request);
    }
}