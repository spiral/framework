<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Http;

use Spiral\Encrypter\EncrypterFactory;
use Spiral\Framework\HttpTest;
use Spiral\Http\Http;

class AuthSessionTest extends HttpTest
{
    public function setUp(): void
    {
        $this->app = $this->makeApp();
        $key = $this->app->get(EncrypterFactory::class)->generateKey();

        $this->app = $this->makeApp([
            'ENCRYPTER_KEY' => $key
        ]);

        $this->http = $this->app->get(Http::class);
    }

    public function testNoToken(): void
    {
        $this->assertSame(
            'none',
            (string)$this->get('/auth/token')->getBody()
        );
    }

    public function testLogin(): void
    {
        $result = $this->get('/auth/login');

        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));
        $this->assertTrue(isset($cookies['sid']));

        $result = $this->get('/auth/token', [], [], $cookies);

        $this->assertNotSame('none', (string)$result->getBody());
    }

    public function testLogout(): void
    {
        $result = $this->get('/auth/login');

        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));
        $this->assertTrue(isset($cookies['sid']));

        $result = $this->get('/auth/token', [], [], $cookies);
        $this->assertNotSame('none', (string)$result->getBody());

        $result = $this->get('/auth/logout', [], [], $cookies);
        $this->assertSame('closed', (string)$result->getBody());

        $result = $this->get('/auth/token', [], [], $cookies);
        $this->assertSame('none', (string)$result->getBody());
    }

    public function testLoginScope(): void
    {
        $result = $this->get('/auth/login2');

        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));
        $this->assertTrue(isset($cookies['sid']));

        $result = $this->get('/auth/token2', [], [], $cookies);

        $this->assertNotSame('none', (string)$result->getBody());
    }

    public function testLoginPayload(): void
    {
        $result = $this->get('/auth/login2');

        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));
        $this->assertTrue(isset($cookies['sid']));

        $result = $this->get('/auth/token3', [], [], $cookies);

        $this->assertSame('{"userID":1}', (string)$result->getBody());
    }
}
