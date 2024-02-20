<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Framework\Spiral;
use Spiral\Http\Config\HttpConfig;
use Spiral\Session\Exception\InvalidSessionContext;
use Spiral\Session\Middleware\SessionMiddleware;
use Spiral\Session\SessionInterface;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

#[TestScope(Spiral::Http)]
final class SessionTest extends HttpTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->enableMiddlewares();
    }

    public function testSetSid(): void
    {
        $this->setHttpHandler(fn (): int => ++$this->session()->getSection('cli')->value);

        $this->fakeHttp()->get('/')->assertOk()->assertBodySame('1')->assertCookieExists('sid');
    }

    public function testSessionResume(): void
    {
        $this->setHttpHandler(fn (): int => ++$this->session()->getSection('cli')->value);

        $result = $this->fakeHttp()->get('/')->assertOk()->assertBodySame('1')->assertCookieExists('sid');

        $this
            ->fakeHttp()
            ->get(uri: '/', cookies: ['sid' => $result->getCookies()['sid']])
            ->assertOk()
            ->assertBodySame('2');

        $this
            ->fakeHttp()
            ->get(uri: '/', cookies: ['sid' => $result->getCookies()['sid']])
            ->assertOk()
            ->assertBodySame('3');
    }

    public function testSessionRegenerateId(): void
    {
        $this->setHttpHandler(fn (): int => ++$this->session()->getSection('cli')->value);

        $result = $this->fakeHttp()->get('/')->assertOk()->assertBodySame('1')->assertCookieExists('sid');

        $this
            ->fakeHttp()
            ->get(uri: '/', cookies: ['sid' => $result->getCookies()['sid']])
            ->assertOk()
            ->assertBodySame('2');

        $this->setHttpHandler(function (): int {
            $this->session()->regenerateID(false);

            return ++$this->session()->getSection('cli')->value;
        });

        $newResult = $this
            ->fakeHttp()
            ->get(uri: '/', cookies: ['sid' => $result->getCookies()['sid']])
            ->assertOk()
            ->assertBodySame('3')
            ->assertCookieExists('sid');

        $this->assertNotEquals($result->getCookies()['sid'], $newResult->getCookies()['sid']);
    }

    public function testDestroySession(): void
    {
        $this->setHttpHandler(fn (): int => ++$this->session()->getSection('cli')->value);

        $result = $this->fakeHttp()->get('/')->assertOk()->assertBodySame('1')->assertCookieExists('sid');

        $this
            ->fakeHttp()
            ->get(
                uri: '/',
                cookies: ['sid' => $result->getCookies()['sid']]
            )
            ->assertOk()
            ->assertBodySame('2');

        $this->setHttpHandler(function () {
            $this->session()->destroy();
            $this->assertFalse($this->session()->isStarted());

            return ++$this->session()->getSection('cli')->value;
        });

        $this
            ->fakeHttp()
            ->get(uri: '/', cookies: ['sid' => $result->getCookies()['sid']])
            ->assertOk()
            ->assertBodySame('1');
    }

    public function testInvalidSessionContextException(): void
    {
        $this->getContainer()->bind(HttpConfig::class, new HttpConfig([
            'middleware' => [],
        ]));

        $this->setHttpHandler(function (): void {
            $this->expectException(InvalidSessionContext::class);
            $this->expectExceptionMessage(\sprintf(
                'The `%s` attribute was not found. To use the session, the `%s` must be configured.',
                SessionMiddleware::ATTRIBUTE,
                SessionMiddleware::class
            ));

            $this->session();
        });

        $this->fakeHttp()->get(uri: '/')->assertOk();
    }

    private function session(): SessionInterface
    {
        return $this->getContainer()->get(SessionInterface::class);
    }
}
