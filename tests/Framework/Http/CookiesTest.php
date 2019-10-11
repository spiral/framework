<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\Http;

use Spiral\Cookies\Cookie;
use Spiral\Cookies\CookieManager;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Framework\HttpTest;
use Spiral\Http\Http;

class CookiesTest extends HttpTest
{
    public function testOutsideOfScopeOK(): void
    {
        $cookies = $this->cookies();
        $this->assertInstanceOf(CookieManager::class, $cookies);
    }

    /**
     * @expectedException \Spiral\Core\Exception\ScopeException
     */
    public function testOutsideOfScopeFail(): void
    {
        $this->cookies()->get('name');
    }

    public function testHasCookie(): void
    {
        $this->http->setHandler(function () {
            return (int)$this->cookies()->has('a');
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('0', $result->getBody()->__toString());
    }

    public function testHasCookie2(): void
    {
        $key = $this->app->get(EncrypterFactory::class)->generateKey();

        $this->app = $this->makeApp([
            'ENCRYPTER_KEY' => $key
        ]);
        $this->http = $this->app->get(Http::class);

        $this->http->setHandler(function () {
            return (int)$this->cookies()->has('a');
        });

        $result = $this->get('/', [], [], [
            'a' => $this->app->get(EncrypterInterface::class)->encrypt('hello')
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());
    }

    public function testGetCookie2(): void
    {
        $key = $this->app->get(EncrypterFactory::class)->generateKey();
        $this->app = $this->makeApp(['ENCRYPTER_KEY' => $key]);
        $this->http = $this->app->get(Http::class);

        $this->http->setHandler(function () {
            return $this->cookies()->get('a');
        });

        $result = $this->get('/', [], [], [
            'a' => $this->app->get(EncrypterInterface::class)->encrypt('hello')
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('hello', $result->getBody()->__toString());
    }

    public function testSetCookie(): void
    {
        $key = $this->app->get(EncrypterFactory::class)->generateKey();
        $this->app = $this->makeApp(['ENCRYPTER_KEY' => $key]);
        $this->http = $this->app->get(Http::class);

        $this->http->setHandler(function () {
            $this->cookies()->set('a', 'value');

            return 'ok';
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('ok', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));

        $this->assertSame(
            'value',
            $this->app->get(EncrypterInterface::class)->decrypt($cookies['a'])
        );
    }

    public function testSetCookie2(): void
    {
        $key = $this->app->get(EncrypterFactory::class)->generateKey();
        $this->app = $this->makeApp(['ENCRYPTER_KEY' => $key]);
        $this->http = $this->app->get(Http::class);

        $this->http->setHandler(function () {
            $this->cookies()->schedule(Cookie::create('a', 'value'));

            return 'ok';
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('ok', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));

        $this->assertSame(
            'value',
            $this->app->get(EncrypterInterface::class)->decrypt($cookies['a'])
        );
    }

    private function cookies(): CookieManager
    {
        return $this->app->get(CookieManager::class);
    }
}
