<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Cookies\Cookie;
use Spiral\Cookies\CookieManager;
use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Core\Exception\ScopeException;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Tests\Framework\HttpTest;

class CookiesTest extends HttpTest
{
    public function setUp(): void
    {
        $this->refreshApp();
    }

    public function testOutsideOfScopeOK(): void
    {
        $cookies = $this->cookies();
        $this->assertInstanceOf(CookieManager::class, $cookies);
    }

    public function testOutsideOfScopeFail(): void
    {
        $this->expectException(ScopeException::class);

        $this->cookies()->get('name');
    }

    public function testHasCookie(): void
    {
        $this->http->setHandler(function () {
            return (int) $this->cookies()->has('a');
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('0', $result->getBody()->__toString());
    }

    public function testHasCookie2(): void
    {
        $this->refreshApp(true);

        $this->http->setHandler(function () {
            return (int) $this->cookies()->has('a');
        });

        $result = $this->get('/', [], [], [
            'a' => $this->app->get(EncrypterInterface::class)->encrypt('hello')
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());
    }

    public function testGetCookie2(): void
    {
        $this->refreshApp(true);

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
        $this->refreshApp(true);

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
        $this->refreshApp(true);

        $this->http->setHandler(function () {
            $this->cookies()->schedule(Cookie::create('a', 'value'));
            $this->assertSame([], $this->cookies()->getAll());
            $this->assertCount(1, $this->cookies()->getScheduled());

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

    public function testDeleteCookie(): void
    {
        $this->refreshApp(true);

        $this->http->setHandler(function () {
            $this->cookies()->delete('cookie');

            return 'ok';
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('ok', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));

        $this->assertSame('', $cookies['cookie']);
    }

    private function cookies(): CookieManager
    {
        return $this->app->get(CookieManager::class);
    }

    private function refreshApp(bool $generateKey = false): void
    {
        if ($generateKey) {
            $key = $this->app->get(EncrypterFactory::class)->generateKey();
        }

        $this->app = $this->makeApp($generateKey ? ['ENCRYPTER_KEY' => $key] : []);

        $this->app->getContainer()
            ->bind(HttpConfig::class, new HttpConfig([
                'middleware' => [CookiesMiddleware::class],
                'basePath' => '/',
                'headers' => []
            ]));

        $this->http = $this->app->get(Http::class);
    }
}
