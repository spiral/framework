<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Session;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Session\Http\SessionStarter;
use Spiral\Tests\Http\HttpTest;

class SessionMiddlewareTest extends HttpTest
{
    public function setUp()
    {
        parent::setUp();

        $config = $this->container->get(ConfiguratorInterface::class)->getConfig(HttpConfig::CONFIG);

        //Flush default middlewares
        $config['middlewares'] = [];

        $this->container->bind(HttpConfig::class, new HttpConfig($config));
    }

    public function testNotSidWhenNotStarted()
    {
        $this->http->riseMiddleware(SessionStarter::class);

        $this->http->setEndpoint(function () {
            return 'all good';
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
    }

    public function testSetSid()
    {
        $this->http->riseMiddleware(SessionStarter::class);

        $this->http->setEndpoint(function () {
            return $this->session->getSection('cli')->value++;
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('SID', $cookies);
    }

    private function fetchCookies(array $header)
    {
        $result = [];

        foreach ($header as $line) {
            $cookie = explode('=', $line);
            $result[$cookie[0]] = substr($cookie[1], 0, strpos($cookie[1], ';'));
        }

        return $result;
    }
}