<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Cookies\CookieManager;
use Spiral\Http\Middlewares\CsrfFirewall;
use Spiral\Http\Middlewares\CsrfMiddleware;
use Spiral\Http\Middlewares\StrictCsrfFirewall;

class CsrfMiddlewareTest extends HttpTest
{
    public function setUp()
    {
        parent::setUp();

        $config = $this->container->get(ConfiguratorInterface::class)->getConfig(HttpConfig::CONFIG);

        //Flush default middlewares
        $config['middlewares'] = [];

        $this->container->bind(HttpConfig::class, new HttpConfig($config));
    }

    public function testNoCsrf()
    {
        $this->http->setEndpoint(function () {
            return 'all good';
        });

        $result = $this->post('/');
        $this->assertSame(200, $result->getStatusCode());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unable to apply CSRF firewall, attribute is missing
     */
    public function testWithCsrfButBroken()
    {
        $this->http->setEndpoint(function () {
            return 'all good';
        });

        $this->http->pushMiddleware($this->container->get(CsrfFirewall::class));

        $result = $this->post('/');
        $this->assertSame(200, $result->getStatusCode());
    }

    public function testMiddlewareToken()
    {
        /** @var HttpConfig $httpConfig */
        $httpConfig = $this->container->get(HttpConfig::class);

        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getAttribute(CsrfMiddleware::ATTRIBUTE);
        });

        $this->http->pushMiddleware($this->container->get(CsrfFirewall::class));

        //Level up
        $this->http->riseMiddleware($this->container->get(CsrfMiddleware::class));

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertNotEmpty($token = $result->getBody()->__toString());

        $this->assertContains($httpConfig->csrfCookie(), $result->getHeaderLine('Set-Cookie'));
    }

    public function testWithCsrfFail()
    {
        $this->http->setEndpoint(function () {
            return 'all good';
        });

        $this->http->pushMiddleware($this->container->get(CsrfFirewall::class));

        //Level up
        $this->http->riseMiddleware($this->container->get(CsrfMiddleware::class));

        $result = $this->post('/');
        $this->assertSame(412, $result->getStatusCode());
    }

    public function testMiddlewareSuccessPOST()
    {
        /** @var HttpConfig $httpConfig */
        $httpConfig = $this->container->get(HttpConfig::class);
        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getAttribute(CsrfMiddleware::ATTRIBUTE);
        });

        $this->http->pushMiddleware($this->container->get(CsrfFirewall::class));

        //Level up
        $this->http->riseMiddleware($this->container->get(CsrfMiddleware::class));

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertNotEmpty($token = $result->getBody()->__toString());

        $result = $this->post('/', [
            CsrfFirewall::PARAMETER => $token
        ], [

        ], [
            $httpConfig->csrfCookie() => $token
        ]);

        $this->assertSame(200, $result->getStatusCode());
    }

    public function testMiddlewareSuccessHeader()
    {
        /** @var HttpConfig $httpConfig */
        $httpConfig = $this->container->get(HttpConfig::class);
        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getAttribute(CsrfMiddleware::ATTRIBUTE);
        });

        $this->http->pushMiddleware($this->container->get(CsrfFirewall::class));

        //Level up
        $this->http->riseMiddleware($this->container->get(CsrfMiddleware::class));

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertNotEmpty($token = $result->getBody()->__toString());

        $result = $this->post('/', [

        ], [
            CsrfFirewall::HEADER => $token
        ], [
            $httpConfig->csrfCookie() => $token
        ]);

        $this->assertSame(200, $result->getStatusCode());
    }

    public function testMiddlewareSuccessHeaderWithCookieManager()
    {
        /** @var HttpConfig $httpConfig */
        $httpConfig = $this->container->get(HttpConfig::class);
        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getAttribute(CsrfMiddleware::ATTRIBUTE);
        });

        $this->http->pushMiddleware($this->container->get(CsrfFirewall::class));

        //Level up
        $this->http->riseMiddleware($this->container->get(CsrfMiddleware::class));
        $this->http->riseMiddleware($this->container->get(CookieManager::class));

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertNotEmpty($token = $result->getBody()->__toString());

        $cookie = rawurldecode(substr(
            $result->getHeaderLine('Set-Cookie'),
            strlen($httpConfig->csrfCookie()) + 1
        ));

        $cookie = substr($cookie, 0, strpos($cookie, ';'));

        $result = $this->post('/', [

        ], [
            CsrfFirewall::HEADER => $token
        ], [
            $httpConfig->csrfCookie() => $cookie
        ]);

        $this->assertSame(200, $result->getStatusCode());
    }

    public function testMiddlewareStict()
    {
        /** @var HttpConfig $httpConfig */
        $httpConfig = $this->container->get(HttpConfig::class);
        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getAttribute(CsrfMiddleware::ATTRIBUTE);
        });

        $this->http->pushMiddleware($this->container->get(StrictCsrfFirewall::class));

        //Level up
        $this->http->riseMiddleware($this->container->get(CsrfMiddleware::class));

        $result = $this->get('/');
        $this->assertSame(412, $result->getStatusCode());
    }

    /**
     * THIS IS EXACTLY WHY YOU SHOULD USE COOKIE MANAGER!
     */
    public function testStrictMiddlewareSuccessHeader()
    {
        /** @var HttpConfig $httpConfig */
        $httpConfig = $this->container->get(HttpConfig::class);
        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getAttribute(CsrfMiddleware::ATTRIBUTE);
        });

        $this->http->pushMiddleware($this->container->get(StrictCsrfFirewall::class));

        //Level up
        $this->http->riseMiddleware($this->container->get(CsrfMiddleware::class));

        $result = $this->get('/', [

        ], [
            CsrfFirewall::HEADER => 'token'
        ], [
            $httpConfig->csrfCookie() => 'token'
        ]);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertNotEmpty($token = $result->getBody()->__toString());

        $result = $this->post('/', [

        ], [
            CsrfFirewall::HEADER => $token
        ], [
            $httpConfig->csrfCookie() => $token
        ]);

        $this->assertSame(200, $result->getStatusCode());
    }

    public function testStrictMiddlewareSuccessHeaderSafe()
    {
        /** @var HttpConfig $httpConfig */
        $httpConfig = $this->container->get(HttpConfig::class);
        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getAttribute(CsrfMiddleware::ATTRIBUTE);
        });

        $this->http->pushMiddleware($this->container->get(StrictCsrfFirewall::class));

        //Level up
        $this->http->riseMiddleware($this->container->get(CsrfMiddleware::class));

        $this->http->riseMiddleware($this->container->get(CookieManager::class));

        $result = $this->get('/', [

        ], [
            CsrfFirewall::HEADER => 'token'
        ], [
            $httpConfig->csrfCookie() => 'token'
        ]);

        $this->assertSame(412, $result->getStatusCode());
    }

    public function testStrictMiddlewareSuccessHeaderSafeBadOrder()
    {
        /** @var HttpConfig $httpConfig */
        $httpConfig = $this->container->get(HttpConfig::class);
        $this->http->setEndpoint(function (ServerRequestInterface $r) {
            return $r->getAttribute(CsrfMiddleware::ATTRIBUTE);
        });

        $this->http->pushMiddleware($this->container->get(StrictCsrfFirewall::class));

        $this->http->riseMiddleware($this->container->get(CookieManager::class));
        $this->http->riseMiddleware($this->container->get(CsrfMiddleware::class));

        $result = $this->get('/', [

        ], [
            CsrfFirewall::HEADER => 'token'
        ], [
            $httpConfig->csrfCookie() => 'token'
        ]);

        $this->assertSame(200, $result->getStatusCode());
    }
}