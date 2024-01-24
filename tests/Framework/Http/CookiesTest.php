<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Cookies\Cookie;
use Spiral\Cookies\CookieManager;
use Spiral\Core\Exception\ScopeException;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Tests\Framework\HttpTestCase;

final class CookiesTest extends HttpTestCase
{
    public const ENV = [
        'ENCRYPTER_KEY' => self::ENCRYPTER_KEY,
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->enableMiddlewares();
    }

    public function testOutsideOfScopeOK(): void
    {
        $this->assertInstanceOf(CookieManager::class, $this->cookies());
    }

    public function testOutsideOfScopeFail(): void
    {
        $this->expectException(ScopeException::class);

        $this->cookies()->get('name');
    }

    public function testHasCookie(): void
    {
        $this
            ->get(uri: '/', handler: fn (): int => (int) $this->cookies()->has('a'))
            ->assertOk()
            ->assertBodySame('0');
    }

    public function testHasCookie2(): void
    {
        $this
            ->get(
                uri: '/',
                cookies: ['a' => $this->getContainer()->get(EncrypterInterface::class)->encrypt('hello')],
                handler: fn (): int => (int)$this->cookies()->has('a')
            )
            ->assertOk()
            ->assertBodySame('1');
    }

    public function testGetCookie2(): void
    {
        $this
            ->get(
                uri: '/',
                cookies: ['a' => $this->getContainer()->get(EncrypterInterface::class)->encrypt('hello')],
                handler: fn (): string => $this->cookies()->get('a')
            )
            ->assertOk()
            ->assertBodySame('hello');
    }

    public function testSetCookie(): void
    {
        $result = $this
            ->get(
                uri:'/',
                handler: function (): string {
                    $this->cookies()->set('a', 'value');
                    return 'ok';
                }
            )
            ->assertOk()
            ->assertBodySame('ok');

        $cookies = $result->getCookies();

        $this->assertSame(
            'value',
            $this->getContainer()->get(EncrypterInterface::class)->decrypt($cookies['a'])
        );
    }

    public function testSetCookie2(): void
    {
        $result = $this
            ->get(
                uri: '/',
                handler: function (): string {
                    $this->cookies()->schedule(Cookie::create('a', 'value'));
                    $this->assertSame([], $this->cookies()->getAll());
                    $this->assertCount(1, $this->cookies()->getScheduled());

                    return 'ok';
                }
            )
            ->assertOk()
            ->assertBodySame('ok');

        $cookies = $result->getCookies();

        $this->assertSame(
            'value',
            $this->getContainer()->get(EncrypterInterface::class)->decrypt($cookies['a'])
        );
    }

    public function testDeleteCookie(): void
    {
        $this
            ->get(
                uri: '/',
                handler: function (): string {
                    $this->cookies()->delete('cookie');
                    return 'ok';
                }
            )
            ->assertOk()
            ->assertBodySame('ok')
            ->assertCookieSame('cookie', '');
    }

    private function cookies(): CookieManager
    {
        return $this->getContainer()->get(CookieManager::class);
    }
}
