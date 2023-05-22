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
        $this->setHttpHandler(function () {
            return (int)$this->cookies()->has('a');
        });

        $this->getHttp()->get('/')
            ->assertOk()
            ->assertBodySame('0');
    }

    public function testHasCookie2(): void
    {
        $this->setHttpHandler(fn(): int => (int)$this->cookies()->has('a'));

        $this->getHttp()->get('/', cookies: [
            'a' => $this->getContainer()->get(EncrypterInterface::class)->encrypt('hello'),
        ])
            ->assertOk()
            ->assertBodySame('1');
    }

    public function testGetCookie2(): void
    {
        $this->setHttpHandler(fn(): string => $this->cookies()->get('a'));

        $this->getHttp()->get('/', cookies: [
            'a' => $this->getContainer()->get(EncrypterInterface::class)->encrypt('hello'),
        ])
            ->assertOk()
            ->assertBodySame('hello');
    }

    public function testSetCookie(): void
    {
        $this->setHttpHandler(function (): string {
            $this->cookies()->set('a', 'value');

            return 'ok';
        });

        $result = $this->getHttp()->get('/')
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
        $this->setHttpHandler(function (): string {
            $this->cookies()->schedule(Cookie::create('a', 'value'));
            $this->assertSame([], $this->cookies()->getAll());
            $this->assertCount(1, $this->cookies()->getScheduled());

            return 'ok';
        });

        $result = $this->getHttp()->get('/')
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
        $this->setHttpHandler(function (): string {
            $this->cookies()->delete('cookie');

            return 'ok';
        });

        $this->getHttp()->get('/')
            ->assertOk()
            ->assertBodySame('ok')
            ->assertCookieSame('cookie', '');
    }

    private function cookies(): CookieManager
    {
        return $this->getContainer()->get(CookieManager::class);
    }
}
