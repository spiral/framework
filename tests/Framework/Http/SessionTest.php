<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Session\Middleware\SessionMiddleware;
use Spiral\Session\SessionInterface;
use Spiral\Tests\Framework\HttpTest;

class SessionTest extends HttpTest
{
    public function setUp(): void
    {
        $this->app = $this->makeApp();

        $this->app->getContainer()
            ->bind(HttpConfig::class, new HttpConfig([
                'middleware' => [SessionMiddleware::class],
                'basePath' => '/'
            ]));

        $this->http = $this->app->get(Http::class);
    }

    public function testSetSid(): void
    {
        $this->http->setHandler(function () {
            return ++$this->session()->getSection('cli')->value;
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('sid', $cookies);
    }

    public function testSessionResume(): void
    {
        $this->http->setHandler(function () {
            return ++$this->session()->getSection('cli')->value;
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('sid', $cookies);
        $result = $this->get('/', [], [], [
            'sid' => $cookies['sid']
        ]);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('2', $result->getBody()->__toString());

        $result = $this->get('/', [], [], ['sid' => $cookies['sid']]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('3', $result->getBody()->__toString());
    }

    public function testSessionRegenerateId(): void
    {
        $this->http->setHandler(function () {
            return ++$this->session()->getSection('cli')->value;
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('sid', $cookies);

        $result = $this->get('/', [], [], [
            'sid' => $cookies['sid']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('2', $result->getBody()->__toString());

        $this->http->setHandler(function () {
            $this->session()->regenerateID(false);

            return ++$this->session()->getSection('cli')->value;
        });

        $result = $this->get('/', [], [], [
            'sid' => $cookies['sid']
        ]);

        $newCookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('sid', $newCookies);
        $this->assertNotEquals($cookies['sid'], $newCookies['sid']);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('3', $result->getBody()->__toString());
    }

    public function testDestroySession(): void
    {
        $this->http->setHandler(function () {
            return ++$this->session()->getSection('cli')->value;
        });

        $result = $this->get('/');
        $this->assertSame(200, $result->getStatusCode());
        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertArrayHasKey('sid', $cookies);
        $result = $this->get('/', [], [], [
            'sid' => $cookies['sid']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('2', $result->getBody()->__toString());
        $this->http->setHandler(function () {
            $this->session()->destroy();
            $this->assertFalse($this->session()->isStarted());

            return ++$this->session()->getSection('cli')->value;
        });
        $result = $this->get('/', [], [], [
            'sid' => $cookies['sid']
        ]);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('1', $result->getBody()->__toString());
    }

    private function session(): SessionInterface
    {
        return $this->app->get(SessionInterface::class);
    }
}
