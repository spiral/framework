<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Router;

use Spiral\Framework\ScopeName;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

final class CoreHandlerTest extends HttpTestCase
{
    #[TestScope(ScopeName::Http)]
    public function testHttpRequestScope(): void
    {
        $this->fakeHttp()->get('/scope/construct')->assertBodySame(ScopeName::HttpRequest->value);
        $this->fakeHttp()->get('/scope/method')->assertBodySame(ScopeName::HttpRequest->value);
    }
}
