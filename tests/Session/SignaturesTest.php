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
use Spiral\Session\SessionInterface;
use Spiral\Tests\Http\HttpTest;

class SignaturesTest extends HttpTest
{
    public function setUp()
    {
        parent::setUp();

        $config = $this->container->get(ConfiguratorInterface::class)->getConfig(HttpConfig::CONFIG);

        //Flush default middlewares
        $config['middlewares'] = [];

        $this->container->bind(HttpConfig::class, new HttpConfig($config));
    }

    public function testSessionResumeButSessionSignatureChanged()
    {
        $this->http->riseMiddleware(SessionStarter::class);

        $this->http->setEndpoint(function () {
            return ++$this->session->getSection('cli')->value;
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());

        $this->assertFalse($this->container->hasInstance(SessionInterface::class));

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('SID', $cookies);

        $oldSID = $cookies['SID'];

        $result = $this->get('/', [], [
            'User-Agent' => 'new client'
        ], [
            'SID' => $cookies['SID']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('SID', $cookies);

        $this->assertNotEquals($oldSID, $cookies['SID']);

        //WILL DESTROY OLD SESSION DATA

        $result = $this->get('/', [], [
            'User-Agent' => 'new client'
        ], [
            'SID' => $cookies['SID']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());

        $newCookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertNotSame($cookies['SID'], $newCookies['SID']);

        //Checking that old session is still OK
        $result = $this->get('/', [], [], [
            'SID' => $oldSID
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());
    }
}