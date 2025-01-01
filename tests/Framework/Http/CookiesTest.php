<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Cookies\Cookie;
use Spiral\Cookies\CookieManager;
use Spiral\Cookies\CookieQueue;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
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

    #[TestScope([Spiral::Http, Spiral::HttpRequest])]
    public function testCookieQueueInScope(): void
    {
        $this->setHttpHandler(static function (ServerRequestInterface $request): void {
            self::assertInstanceOf(
                CookieQueue::class,
                ContainerScope::getContainer()->get(ServerRequestInterface::class)->getAttribute(CookieQueue::ATTRIBUTE)
            );

            self::assertSame(
                ContainerScope::getContainer()
                    ->get(ServerRequestInterface::class)
                    ->getAttribute(CookieQueue::ATTRIBUTE),
                $request->getAttribute(CookieQueue::ATTRIBUTE)
            );

            self::assertSame(
                ContainerScope::getContainer()
                    ->get(ServerRequestInterface::class)
                    ->getAttribute(CookieQueue::ATTRIBUTE),
                ContainerScope::getContainer()->get(CookieQueue::class)
            );
        });

        $this->fakeHttp()->get('/')->assertOk();
    }

    #[TestScope([Spiral::Http, Spiral::HttpRequest])]
    public function testHasCookie(): void
    {
        $this->setHttpHandler(fn(ServerRequestInterface $request): int => (int)$this->cookies()->has('a'));

        $this->fakeHttp()->get('/')->assertOk()->assertBodySame('0');
    }

    #[TestScope([Spiral::Http, Spiral::HttpRequest])]
    public function testHasCookie2(): void
    {
        $this->setHttpHandler(fn(ServerRequestInterface $request): int => (int)$this->cookies()->has('a'));

        $this
            ->fakeHttp()
            ->get(
                uri: '/',
                cookies: ['a' => $this->getContainer()->get(EncrypterInterface::class)->encrypt('hello')]
            )
            ->assertOk()
            ->assertBodySame('1');
    }

    #[TestScope([Spiral::Http, Spiral::HttpRequest])]
    public function testGetCookie2(): void
    {
        $this->setHttpHandler(fn(ServerRequestInterface $request): mixed => $this->cookies()->get('a'));

        $this
            ->fakeHttp()
            ->get(
                uri: '/',
                cookies: ['a' => $this->getContainer()->get(EncrypterInterface::class)->encrypt('hello')]
            )
            ->assertOk()
            ->assertBodySame('hello');
    }

    #[TestScope([Spiral::Http, Spiral::HttpRequest])]
    public function testSetCookie(): void
    {
        $this->setHttpHandler(function (ServerRequestInterface $request) {
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

    #[TestScope([Spiral::Http, Spiral::HttpRequest])]
    public function testSetCookie2(): void
    {
        $this->setHttpHandler(function (ServerRequestInterface $request): string {
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

    #[TestScope([Spiral::Http, Spiral::HttpRequest])]
    public function testDeleteCookie(): void
    {
        $this->setHttpHandler(function (ServerRequestInterface $request): string {
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
