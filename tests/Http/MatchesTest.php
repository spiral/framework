<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Http\Routing\Route;
use TestApplication\Controllers\DummyController;

class MatchesTest extends HttpTest
{
    public function testControllerMatches()
    {
        $this->http->addRoute(new Route('default', '[<id>]', DummyController::class . ':matches'));

        $response = $this->get('/123');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"id":"123"}', (string)$response->getBody());

        $response = $this->get('/');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"id":null}', (string)$response->getBody());
    }
}