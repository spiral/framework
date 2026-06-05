<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Bootloader\Broadcasting;

use Spiral\Broadcasting\Middleware\AuthorizationMiddleware;
use Spiral\Tests\Framework\BaseTestCase;

final class WebsocketsBootloaderTest extends BaseTestCase
{
    public function testAuthorizationMiddlewareBinding(): void
    {
        $this->assertContainerBoundAsSingleton(AuthorizationMiddleware::class, AuthorizationMiddleware::class);
    }
}
