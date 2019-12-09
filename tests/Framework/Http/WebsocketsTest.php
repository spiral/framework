<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Framework\HttpTest;
use Spiral\Http\Http;

class WebsocketsTest extends HttpTest
{
    public function testBypass(): void
    {
        $this->init();
        $this->assertSame('Hello, Dave.', (string)$this->get('/index')->getBody());
    }

    public function testEmptyServerOK(): void
    {
        $this->init();

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getAttribute('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function testAllowServerAccess(): void
    {
        $this->init(static function () {
            return true;
        });

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getAttribute('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function testDenyServerAccess(): void
    {
        $this->init(static function () {
            return false;
        });

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(403, $this->getAttribute('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function testServerAccessWithDependency(): void
    {
        $this->init(static function (ServerRequestInterface $request) {
            return $request instanceof ServerRequestInterface;
        });

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getAttribute('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function testServerAccessFunction(): void
    {
        $this->init('closelog');

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getAttribute('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }


    public function testServerAccessCallback(): void
    {
        $this->init([$this, 'ok']);

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getAttribute('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function ok(ServerRequestInterface $request)
    {
        return $request instanceof ServerRequestInterface;
    }

    private function init(callable $server = null, callable $topic = null, callable $topicWildcard = null): void
    {
        $this->app = $this->makeApp([
            'RR_BROADCAST_PATH'          => '/ws/',
            'WS_SERVER_CALLBACK'         => $server,
            'WS_TOPIC_CALLBACK'          => $topic,
            'WS_TOPIC_WILDCARD_CALLBACK' => $topicWildcard,
        ]);

        $this->http = $this->app->get(Http::class);
    }
}
