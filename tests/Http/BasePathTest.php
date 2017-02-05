<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Routing\Route;
use TestApplication\Controllers\DummyController;

class BasePathTest extends HttpTest
{

    public function setUp()
    {
        parent::setUp();

        $config = $this->container->get(ConfiguratorInterface::class)->getConfig(HttpConfig::CONFIG);

        //Flush default middlewares
        $config['basePath'] = '/prefix';
        $config['middlewares'] = [];

        $this->container->bind(HttpConfig::class, new HttpConfig($config));
    }

    public function testPropagnation()
    {
        $this->http->defaultRoute(new Route('default', '/abc', DummyController::class . ':index'));

        $route = $this->http->getRouter()->getRoute('default');
        $this->assertSame('/prefix/', $route->getPrefix());
    }

    /**
     * @expectedException \Spiral\Http\Exceptions\ClientException
     */
    public function testNotFoundDuePrefix()
    {
        $this->http->defaultRoute(new Route('default', '/abc', DummyController::class . ':index'));
        $this->http->setRouter($this->http->getRouter());

        $response = $this->get('/abc');
    }

    public function testWithBasePath()
    {
        $this->http->defaultRoute(new Route('default', '/abc', DummyController::class . ':index'));

        $response = $this->get('/prefix/abc');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello, Dave.', (string)$response->getBody());
    }

    public function testUriGenerationWithBasePath()
    {
        $this->http->defaultRoute(new Route('default', '/abc', DummyController::class . ':index'));
        $this->assertSame('/prefix/abc', (string)$this->http->getRouter()->uri('default'));
    }

    public function testUriGenerationWithParameters()
    {
        $this->http->defaultRoute(
            new Route('default', '/abc/<id>',
                DummyController::class . ':index')
        );

        $this->assertSame('/prefix/abc/100', (string)$this->http->getRouter()->uri('default', [
            'id' => 100
        ]));
    }

    public function testUriGenerationWithParametersAndQuery()
    {
        $this->http->defaultRoute(
            new Route('default', '/abc/<id>',
                DummyController::class . ':index')
        );

        $this->assertSame('/prefix/abc/100?action=reload', (string)$this->http->getRouter()->uri('default', [
            'id'     => 100,
            'action' => 'reload'
        ]));
    }
}