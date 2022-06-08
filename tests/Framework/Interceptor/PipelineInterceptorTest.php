<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Interceptor;

use Spiral\Tests\Framework\HttpTest;

final class PipelineInterceptorTest extends HttpTest
{
    public function testWithoutPipeline(): void
    {
        $this->getHttp()->get('/intercepted/without')
            ->assertBodySame('["without","three","two","one"]');
    }

    public function testWith(): void
    {
        $this->getHttp()->get('/intercepted/with')
            ->assertBodySame('["with","three","two","one"]');
    }

    public function testMix(): void
    {
        //pipeline interceptors are injected into the middle
        $this->getHttp()->get('/intercepted/mix')
            ->assertBodySame('["mix","six","three","two","one","five","four"]');
    }

    public function testDup(): void
    {
        //pipeline interceptors are added to the end
        $this->getHttp()->get('/intercepted/dup')
            ->assertBodySame('["dup","three","two","one","three","two","one"]');
    }

    public function testSkipNext(): void
    {
        //interceptors after current pipeline are ignored
        $this->getHttp()->get('/intercepted/skip')
            ->assertBodySame('["skip","three","two","one","one"]');
    }

    public function testSkipIfFirst(): void
    {
        //interceptors after current pipeline are ignored
        $this->getHttp()->get('/intercepted/first')
            ->assertBodySame('["first","three","two","one"]');
    }

    public function testWithAttribute(): void
    {
        $this->getHttp()->get('/intercepted/withAttribute')
            ->assertBodySame('["withAttribute","three","two","one"]');
    }

    public function testMixAttribute(): void
    {
        $this->getHttp()->get('/intercepted/mixAttribute')
            ->assertBodySame('["mixAttribute","six","three","two","one","five","four"]');
    }

    public function testDupAttribute(): void
    {
        $this->getHttp()->get('/intercepted/dupAttribute')
            ->assertBodySame('["dupAttribute","three","two","one","three","two","one"]');
    }

    public function testSkipNextAttribute(): void
    {
        $this->getHttp()->get('/intercepted/skipAttribute')
            ->assertBodySame('["skipAttribute","three","two","one","one"]');
    }

    public function testSkipIfFirstAttribute(): void
    {
        $this->getHttp()->get('/intercepted/firstAttribute')
            ->assertBodySame('["firstAttribute","three","two","one"]');
    }
}
