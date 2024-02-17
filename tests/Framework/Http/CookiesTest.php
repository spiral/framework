<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Cookies\Cookie;
use Spiral\Cookies\CookieManager;
use Spiral\Core\Exception\ScopeException;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

#[TestScope([Spiral::Http, Spiral::HttpRequest])]
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
        $this->setHttpHandler(function (ServerRequestInterface $request) {
            $this->getContainer()->bindSingleton(ServerRequestInterface::class, $request);

            return (int)$this->cookies()->has('a');
        });

        $this->fakeHttp()->get('/')->assertOk()->assertBodySame('0');
    }

    public function testHasCookie2(): void
    {
        $this->setHttpHandler(function (ServerRequestInterface $request) {
            $this->getContainer()->bindSingleton(ServerRequestInterface::class, $request);

            return (int)$this->cookies()->has('a');
        });

        $this
            ->fakeHttp()
            ->get(
                uri: '/',
                cookies: ['a' => $this->getContainer()->get(EncrypterInterface::class)->encrypt('hello')]
            )
            ->assertOk()
            ->assertBodySame('1');
    }

    public function testGetCookie2(): void
    {
        $this->setHttpHandler(function (ServerRequestInterface $request) {
            $this->getContainer()->bindSingleton(ServerRequestInterface::class, $request);

            return $this->cookies()->get('a');
        });

        $this
            ->fakeHttp()
            ->get(
                uri: '/',
                cookies: ['a' => $this->getContainer()->get(EncrypterInterface::class)->encrypt('hello')]
            )
            ->assertOk()
            ->assertBodySame('hello');
    }

    public function testSetCookie(): void
    {
        $this->setHttpHandler(function (ServerRequestInterface $request) {
            $this->getContainer()->bindSingleton(ServerRequestInterface::class, $request);

            $this->cookies()->set('a', 'value');
            return 'ok';
        });

        $result = $this->fakeHttp()->get('/')->assertOk()->assertBodySame('ok');

        $cookies = $result->getCookies();

        $this->assertSame(
            'value',
            $this->getContainer()->get(EncrypterInterface::class)->decrypt($cookies['a'])
        );
    }

    public function testSetCookie2(): void
    {
        $this->setHttpHandler(function (ServerRequestInterface $request): string {
            $this->getContainer()->bindSingleton(ServerRequestInterface::class, $request);

            $this->cookies()->schedule(Cookie::create('a', 'value'));
            $this->assertSame([], $this->cookies()->getAll());
            $this->assertCount(1, $this->cookies()->getScheduled());

            return 'ok';
        });

        $result = $this->fakeHttp()->get('/')->assertOk()->assertBodySame('ok');

        $cookies = $result->getCookies();

        $this->assertSame(
            'value',
            $this->getContainer()->get(EncrypterInterface::class)->decrypt($cookies['a'])
        );
    }

    public function testDeleteCookie(): void
    {
        $this->setHttpHandler(function (ServerRequestInterface $request): string {
            $this->getContainer()->bindSingleton(ServerRequestInterface::class, $request);
            $this->cookies()->delete('cookie');
            return 'ok';
        });

        $this->fakeHttp()->get('/')->assertOk()->assertBodySame('ok')->assertCookieSame('cookie', '');
    }

    private function cookies(): CookieManager
    {
        return $this->getContainer()->get(CookieManager::class);
    }
}
