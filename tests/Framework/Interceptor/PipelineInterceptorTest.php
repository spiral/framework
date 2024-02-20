<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Interceptor;

use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

#[TestScope(Spiral::Http)]
final class PipelineInterceptorTest extends HttpTestCase
{
    public function testWithoutPipeline(): void
    {
        $this->fakeHttp()->get('/intercepted/without')->assertBodySame('["without","three","two","one"]');
    }

    public function testWith(): void
    {
        $this->fakeHttp()->get('/intercepted/with')->assertBodySame('["with","three","two","one"]');
    }

    public function testMix(): void
    {
        //pipeline interceptors are injected into the middle
        $this->fakeHttp()->get('/intercepted/mix')->assertBodySame('["mix","six","three","two","one","five","four"]');
    }

    public function testDup(): void
    {
        //pipeline interceptors are added to the end
        $this->fakeHttp()->get('/intercepted/dup')->assertBodySame('["dup","three","two","one","three","two","one"]');
    }

    public function testSkipNext(): void
    {
        //interceptors after current pipeline are ignored
        $this->fakeHttp()->get('/intercepted/skip')->assertBodySame('["skip","three","two","one","one"]');
    }

    public function testSkipIfFirst(): void
    {
        //interceptors after current pipeline are ignored
        $this->fakeHttp()->get('/intercepted/first')->assertBodySame('["first","three","two","one"]');
    }
}
