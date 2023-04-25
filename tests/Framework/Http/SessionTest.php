<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Session\SessionInterface;
use Spiral\Tests\Framework\HttpTestCase;

final class SessionTest extends HttpTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->enableMiddlewares();

        $this->setHttpHandler(function () {
            return ++$this->session()->getSection('cli')->value;
        });
    }

    public function testSetSid(): void
    {
        $this->getHttp()->get('/')
            ->assertOk()
            ->assertBodySame('1')
            ->assertCookieExists('sid');
    }

    public function testSessionResume(): void
    {
        $result = $this->getHttp()->get('/')
            ->assertOk()
            ->assertBodySame('1')
            ->assertCookieExists('sid');

        $this->getHttp()->get('/', cookies: [
            'sid' => $result->getCookies()['sid'],
        ])->assertOk()->assertBodySame('2');

        $this->getHttp()->get('/', cookies: [
            'sid' => $result->getCookies()['sid'],
        ])->assertOk()->assertBodySame('3');
    }

    public function testSessionRegenerateId(): void
    {
        $result = $this->getHttp()->get('/')
            ->assertOk()
            ->assertBodySame('1')
            ->assertCookieExists('sid');

        $this->getHttp()->get('/', cookies: [
            'sid' => $result->getCookies()['sid'],
        ])->assertOk()->assertBodySame('2');

        $this->setHttpHandler(function () {
            $this->session()->regenerateID(false);

            return ++$this->session()->getSection('cli')->value;
        });

        $newResult = $this->getHttp()->get('/', cookies: [
            'sid' => $result->getCookies()['sid'],
        ])
            ->assertOk()
            ->assertBodySame('3')
            ->assertCookieExists('sid');

        $this->assertNotEquals($result->getCookies()['sid'], $newResult->getCookies()['sid']);
    }

    public function testDestroySession(): void
    {
        $result = $this->getHttp()->get('/')
            ->assertOk()
            ->assertBodySame('1')
            ->assertCookieExists('sid');

        $this->getHttp()->get('/', cookies: [
            'sid' => $result->getCookies()['sid'],
        ])->assertOk()->assertBodySame('2');

        $this->setHttpHandler(function () {
            $this->session()->destroy();
            $this->assertFalse($this->session()->isStarted());

            return ++$this->session()->getSection('cli')->value;
        });

        $this->getHttp()->get('/', cookies: [
            'sid' => $result->getCookies()['sid'],
        ])->assertOk()->assertBodySame('1');
    }

    private function session(): SessionInterface
    {
        return $this->getContainer()->get(SessionInterface::class);
    }
}
