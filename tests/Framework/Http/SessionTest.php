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
    }

    public function testSetSid(): void
    {
        $this->get(uri: '/', handler: fn (): int => ++$this->session()->getSection('cli')->value)
            ->assertOk()
            ->assertBodySame('1')
            ->assertCookieExists('sid');
    }

    public function testSessionResume(): void
    {
        $result = $this->get(uri: '/', handler: fn (): int => ++$this->session()->getSection('cli')->value)
            ->assertOk()
            ->assertBodySame('1')
            ->assertCookieExists('sid');

        $this
            ->get(
                uri: '/',
                cookies: ['sid' => $result->getCookies()['sid']],
                handler: fn (): int => ++$this->session()->getSection('cli')->value
            )
            ->assertOk()
            ->assertBodySame('2');

        $this
            ->get(
                uri: '/',
                cookies: ['sid' => $result->getCookies()['sid']],
                handler: fn (): int => ++$this->session()->getSection('cli')->value
            )
            ->assertOk()
            ->assertBodySame('3');
    }

    public function testSessionRegenerateId(): void
    {
        $result = $this->get(uri: '/', handler: fn (): int => ++$this->session()->getSection('cli')->value)
            ->assertOk()
            ->assertBodySame('1')
            ->assertCookieExists('sid');

        $this
            ->get(
                uri: '/',
                cookies: ['sid' => $result->getCookies()['sid']],
                handler: fn (): int => ++$this->session()->getSection('cli')->value
            )
            ->assertOk()
            ->assertBodySame('2');

        $newResult = $this
            ->get(
                uri: '/',
                cookies: ['sid' => $result->getCookies()['sid']],
                handler: function () {
                    $this->session()->regenerateID(false);

                    return ++$this->session()->getSection('cli')->value;
                }
            )
            ->assertOk()
            ->assertBodySame('3')
            ->assertCookieExists('sid');

        $this->assertNotEquals($result->getCookies()['sid'], $newResult->getCookies()['sid']);
    }

    public function testDestroySession(): void
    {
        $result = $this->get(uri: '/', handler: fn (): int => ++$this->session()->getSection('cli')->value)
            ->assertOk()
            ->assertBodySame('1')
            ->assertCookieExists('sid');

        $this
            ->get(
                uri: '/',
                cookies: ['sid' => $result->getCookies()['sid']],
                handler: fn (): int => ++$this->session()->getSection('cli')->value
            )
            ->assertOk()
            ->assertBodySame('2');

        $this
            ->get(
                uri: '/',
                cookies: ['sid' => $result->getCookies()['sid']],
                handler: function () {
                    $this->session()->destroy();
                    $this->assertFalse($this->session()->isStarted());

                    return ++$this->session()->getSection('cli')->value;
                }
            )
            ->assertOk()
            ->assertBodySame('1');
    }

    private function session(): SessionInterface
    {
        return $this->getContainer()->get(SessionInterface::class);
    }
}
