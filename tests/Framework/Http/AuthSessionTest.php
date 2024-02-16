<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

#[TestScope(Spiral::Http)]
final class AuthSessionTest extends HttpTestCase
{
    public const ENV = [
        'ENCRYPTER_KEY' => self::ENCRYPTER_KEY,
    ];

    public function testNoToken(): void
    {
        $this->fakeHttp()->get(uri: '/auth/token')->assertBodySame('none');
    }

    public function testLogin(): void
    {
        $result = $this->fakeHttp()->get(uri: '/auth/login')
            ->assertBodySame('OK')
            ->assertCookieExists('token')
            ->assertCookieExists('sid');

        $this->fakeHttp()->get(uri: '/auth/token', cookies: $result->getCookies())->assertBodyNotSame('none');
    }

    public function testLogout(): void
    {
        $result = $this->fakeHttp()->get(uri: '/auth/login')
            ->assertBodySame('OK')
            ->assertCookieExists('token')
            ->assertCookieExists('sid');

        $this->fakeHttp()->get(uri: '/auth/token', cookies: $result->getCookies())->assertBodyNotSame('none');
        $this->fakeHttp()->get(uri: '/auth/token', cookies: $result->getCookies())->assertBodyNotSame('none');
        $this->fakeHttp()->get(uri: '/auth/logout', cookies: $result->getCookies())->assertBodySame('closed');
        $this->fakeHttp()->get(uri: '/auth/token', cookies: $result->getCookies())->assertBodySame('none');
    }

    public function testLoginScope(): void
    {
        $result = $this->fakeHttp()->get('/auth/login2')
            ->assertBodySame('OK')
            ->assertCookieExists('token')
            ->assertCookieExists('sid');

        $this->fakeHttp()->get('/auth/token2', cookies: $result->getCookies())->assertBodyNotSame('none');
    }

    public function testLoginPayload(): void
    {
        $result = $this->fakeHttp()->get('/auth/login2')
            ->assertBodySame('OK')
            ->assertCookieExists('token')
            ->assertCookieExists('sid');

        $this->fakeHttp()->get('/auth/token3', cookies: $result->getCookies())->assertBodySame('{"userID":1}');
    }
}
