<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Router;

use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

final class CoreHandlerTest extends HttpTestCase
{
    #[TestScope(Spiral::Http)]
    public function testHttpRequestScope(): void
    {
        $this->fakeHttp()->get('/scope/construct')->assertBodySame(Spiral::HttpRequest->value);
        $this->fakeHttp()->get('/scope/method')->assertBodySame(Spiral::HttpRequest->value);
    }
}
