<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Tests\Framework\HttpTest;

final class AuthSessionTest extends HttpTest
{
    public const ENV = [
        'ENCRYPTER_KEY' => self::ENCRYPTER_KEY,
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->enableMiddlewares();
    }

    public function testNoToken(): void
    {
        $this->getHttp()->get(uri: '/auth/token')
            ->assertBodySame('none');
    }

    public function testLogin(): void
    {
        $result = $this->getHttp()->get(uri: '/auth/login')
            ->assertBodySame('OK')
            ->assertCookieExists('token')
            ->assertCookieExists('sid');

        $this->getHttp()->get(uri: '/auth/token', cookies: $result->getCookies())
            ->assertBodyNotSame('none');
    }

    public function testLogout(): void
    {
        $result = $this->getHttp()->get(uri: '/auth/login')
            ->assertBodySame('OK')
            ->assertCookieExists('token')
            ->assertCookieExists('sid');

        $this->getHttp()->get(uri: '/auth/token', cookies: $result->getCookies())
            ->assertBodyNotSame('none');

        $this->getHttp()->get(uri: '/auth/logout', cookies: $result->getCookies())
            ->assertBodySame('closed');

        $this->getHttp()->get(uri: '/auth/token', cookies: $result->getCookies())
            ->assertBodySame('none');
    }

    public function testLoginScope(): void
    {
        $result = $this->getHttp()->get('/auth/login2')
            ->assertBodySame('OK')
            ->assertCookieExists('token')
            ->assertCookieExists('sid');

        $this->getHttp()->get('/auth/token2', cookies: $result->getCookies())
            ->assertBodyNotSame('none');
    }

    public function testLoginPayload(): void
    {
        $result = $this->getHttp()->get('/auth/login2')
            ->assertBodySame('OK')
            ->assertCookieExists('token')
            ->assertCookieExists('sid');

        $this->getHttp()->get('/auth/token3', cookies: $result->getCookies())
            ->assertBodySame('{"userID":1}');
    }
}
