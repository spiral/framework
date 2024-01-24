<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Router;

use Spiral\Framework\ScopeName;
use Spiral\Tests\Framework\HttpTestCase;

final class CoreHandlerTest extends HttpTestCase
{
    public function testHttpRequestScope(): void
    {
        $this->get('/scope/construct')->assertBodySame(ScopeName::HttpRequest->value);
        $this->get('/scope/method')->assertBodySame(ScopeName::HttpRequest->value);
    }
}
