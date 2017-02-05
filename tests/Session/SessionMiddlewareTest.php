<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Session;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Cookies\CookieManager;
use Spiral\Session\Http\SessionStarter;
use Spiral\Session\SessionInterface;
use Spiral\Session\SectionInterface;
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

    public function testSectionByInjection()
    {
        $this->http->riseMiddleware(SessionStarter::class);

        $this->http->setEndpoint(function () {
            $this->assertSame(
                'cli',
                $this->container->get(SectionInterface::class, 'cli')->getName()
            );
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('SID', $cookies);
    }

    public function testSessionResume()
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

        $result = $this->get('/', [], [], [
            'SID' => $cookies['SID']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('2', $result->getBody()->__toString());

        $result = $this->get('/', [], [], [
            'SID' => $cookies['SID']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('3', $result->getBody()->__toString());
    }

    public function testSessionRegenerateId()
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

        $result = $this->get('/', [], [], [
            'SID' => $cookies['SID']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('2', $result->getBody()->__toString());

        $this->http->setEndpoint(function () {
            $this->session->regenerateID(false);

            return ++$this->session->getSection('cli')->value;
        });

        $result = $this->get('/', [], [], [
            'SID' => $cookies['SID']
        ]);

        $newCookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('SID', $newCookies);

        $this->assertNotEquals($cookies['SID'], $newCookies['SID']);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('3', $result->getBody()->__toString());
    }

    public function testSetSidWithCookieManager()
    {
        $this->http->riseMiddleware(SessionStarter::class);
        $this->http->riseMiddleware(CookieManager::class);

        $this->http->setEndpoint(function () {
            return $this->session->getSection('cli')->value++;
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('SID', $cookies);
    }

    public function testSetSidWithCookieManagerResume()
    {
        $this->http->riseMiddleware(SessionStarter::class);
        $this->http->riseMiddleware(CookieManager::class);

        $this->http->setEndpoint(function () {
            $this->assertInternalType('array', $this->session->__debugInfo());

            return ++$this->session->getSection('cli')->value;
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('SID', $cookies);

        $result = $this->get('/', [], [], [
            'SID' => $cookies['SID']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('2', $result->getBody()->__toString());


        $result = $this->get('/', [], [], [
            'SID' => $cookies['SID']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('3', $result->getBody()->__toString());
    }

    public function testDestroySession()
    {
        $this->http->riseMiddleware(SessionStarter::class);
        $this->http->riseMiddleware(CookieManager::class);

        $this->http->setEndpoint(function () {
            $this->assertInternalType('array', $this->session->__debugInfo());

            return ++$this->session->getSection('cli')->value;
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('SID', $cookies);

        $result = $this->get('/', [], [], [
            'SID' => $cookies['SID']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('2', $result->getBody()->__toString());

        $this->http->setEndpoint(function () {
            $this->session->destroy();
            $this->assertFalse($this->session->isStarted());

            return ++$this->session->getSection('cli')->value;
        });

        $result = $this->get('/', [], [], [
            'SID' => $cookies['SID']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());
    }
}