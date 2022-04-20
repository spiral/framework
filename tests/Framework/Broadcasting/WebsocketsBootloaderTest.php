<?php

declare(strict_types=1);

namespace Framework\Broadcasting;

use Spiral\Broadcasting\Middleware\AuthorizationMiddleware;
use Spiral\Http\Config\HttpConfig;
use Spiral\Tests\Framework\BaseTest;

final class WebsocketsBootloaderTest extends BaseTest
{
    public function testMiddlewareShouldBeSkippedIfAuthorizationPathIsNotSpecified(): void
    {
        $app = $this->makeApp();

        $middleware = $app->getContainer()->get(HttpConfig::class)->getMiddleware();

        $this->assertNotContains(AuthorizationMiddleware::class, $middleware);
    }


    public function testMiddlewareShouldNotBeSkippedIfAuthorizationPathIsSpecified(): void
    {
        $app = $this->makeApp([
            'BROADCAST_AUTHORIZE_PATH' => 'foo'
        ]);

        $middleware = $app->getContainer()->get(HttpConfig::class)->getMiddleware();

        $this->assertContains(AuthorizationMiddleware::class, $middleware);
    }
}
