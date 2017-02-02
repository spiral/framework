<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Mockery as m;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Cookies\CookieManager;
use Spiral\Http\Cookies\CookieQueue;

class CookieManagerTest extends HttpTest
{
    public function setUp()
    {
        parent::setUp();

        $config = $this->container->get(ConfiguratorInterface::class)->getConfig(HttpConfig::CONFIG);

        //Flush default middlewares
        $config['middlewares'] = [];

        $this->container->bind(HttpConfig::class, new HttpConfig($config));
    }

    public function testSetCookieWithUnprotected()
    {
        $config = $this->container->get(HttpConfig::class);
        $mock = m::mock($config);

        $mock->shouldReceive('cookieProtection')->andReturn(
            HttpConfig::COOKIE_UNPROTECTED
        );

        $this->http->pushMiddleware(new CookieManager($mock, $this->container));

        $this->http->setEndpoint(function () {
            $this->assertInstanceOf(CookieQueue::class, $this->cookies);

            $this->cookies->set('test', 'cookie-value-!@$');

            return 'result';
        });

        $result = $this->get('/');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('result', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('test', $cookies);

        $this->assertEquals(rawurldecode($cookies['test']), 'cookie-value-!@$');

        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $this->input->cookie('test');
        });

        $result = $this->get('/', [], [], [
            'test' => rawurldecode($cookies['test'])
        ]);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('cookie-value-!@$', $result->getBody()->__toString());
    }

    public function testDeleteCookieWithUnprotected()
    {
        $config = $this->container->get(HttpConfig::class);
        $mock = m::mock($config);

        $mock->shouldReceive('cookieProtection')->andReturn(
            HttpConfig::COOKIE_ENCRYPT
        );

        $this->http->pushMiddleware(new CookieManager($mock, $this->container));

        $this->http->setEndpoint(function () {
            $this->assertInstanceOf(CookieQueue::class, $this->cookies);

            $this->cookies->delete('test');

            return 'result';
        });

        $result = $this->get('/');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('result', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('test', $cookies);

        $this->assertEmpty($cookies['test']);
    }

    public function testDeleteCookieWithUnprotectedShelduled()
    {
        $config = $this->container->get(HttpConfig::class);
        $mock = m::mock($config);

        $mock->shouldReceive('cookieProtection')->andReturn(
            HttpConfig::COOKIE_ENCRYPT
        );

        $this->http->pushMiddleware(new CookieManager($mock, $this->container));

        $this->http->setEndpoint(function () {
            $this->assertInstanceOf(CookieQueue::class, $this->cookies);

            $this->cookies->set('test', 'abc');
            $this->cookies->delete('test');

            return 'result';
        });

        $result = $this->get('/');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('result', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('test', $cookies);

        $this->assertEmpty($cookies['test']);
    }

    public function testSetCookieWithHMAC()
    {
        $config = $this->container->get(HttpConfig::class);
        $mock = m::mock($config);

        $mock->shouldReceive('cookieProtection')->andReturn(
            HttpConfig::COOKIE_HMAC
        );

        $this->http->pushMiddleware(new CookieManager($mock, $this->container));

        $this->http->setEndpoint(function () {
            $this->assertInstanceOf(CookieQueue::class, $this->cookies);

            $this->cookies->set('test', 'cookie-value-!@$');

            return 'result';
        });

        $result = $this->get('/');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('result', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('test', $cookies);

        $this->assertNotEquals(rawurldecode($cookies['test']), 'cookie-value-!@$');

        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getCookieParams()['test'];
        });

        $result = $this->get('/', [], [], [
            'test' => rawurldecode($cookies['test'])
        ]);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('cookie-value-!@$', $result->getBody()->__toString());
    }

    public function testSetCookieWithEncrypt()
    {
        $config = $this->container->get(HttpConfig::class);
        $mock = m::mock($config);

        $mock->shouldReceive('cookieProtection')->andReturn(
            HttpConfig::COOKIE_ENCRYPT
        );

        $this->http->pushMiddleware(new CookieManager($mock, $this->container));

        $this->http->setEndpoint(function () {
            $this->assertInstanceOf(CookieQueue::class, $this->cookies);

            $this->cookies->set('test', 'cookie-value-!@$');

            return 'result';
        });

        $result = $this->get('/');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('result', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('test', $cookies);

        $this->assertNotEquals(rawurldecode($cookies['test']), 'cookie-value-!@$');

        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getCookieParams()['test'];
        });

        $result = $this->get('/', [], [], [
            'test' => rawurldecode($cookies['test'])
        ]);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('cookie-value-!@$', $result->getBody()->__toString());
    }

    public function testInvalidCookie()
    {
        $config = $this->container->get(HttpConfig::class);
        $mock = m::mock($config);

        $mock->shouldReceive('cookieProtection')->andReturn(
            HttpConfig::COOKIE_ENCRYPT
        );

        $this->http->pushMiddleware(new CookieManager($mock, $this->container));

        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getCookieParams()['test'];
        });

        $result = $this->get('/', [], [], [
            'test' => 'test'
        ]);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertEmpty($result->getBody()->__toString());
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