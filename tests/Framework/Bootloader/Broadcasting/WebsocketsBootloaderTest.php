<?php

declare(strict_types=1);

namespace Framework\Bootloader\Broadcasting;

use Spiral\Broadcasting\Middleware\AuthorizationMiddleware;
use Spiral\Tests\Framework\BaseTest;

final class WebsocketsBootloaderTest extends BaseTest
{
    public function testAuthorizationMiddlewareBinding(): void
    {
        $this->assertContainerBoundAsSingleton(AuthorizationMiddleware::class, AuthorizationMiddleware::class);
    }
}
