<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Interceptor;

use Spiral\Tests\Framework\HttpTestCase;

final class PipelineInterceptorTest extends HttpTestCase
{
    public function testWithoutPipeline(): void
    {
        $this->get('/intercepted/without')->assertBodySame('["without","three","two","one"]');
    }

    public function testWith(): void
    {
        $this->get('/intercepted/with')->assertBodySame('["with","three","two","one"]');
    }

    public function testMix(): void
    {
        //pipeline interceptors are injected into the middle
        $this->get('/intercepted/mix')->assertBodySame('["mix","six","three","two","one","five","four"]');
    }

    public function testDup(): void
    {
        //pipeline interceptors are added to the end
        $this->get('/intercepted/dup')->assertBodySame('["dup","three","two","one","three","two","one"]');
    }

    public function testSkipNext(): void
    {
        //interceptors after current pipeline are ignored
        $this->get('/intercepted/skip')->assertBodySame('["skip","three","two","one","one"]');
    }

    public function testSkipIfFirst(): void
    {
        //interceptors after current pipeline are ignored
        $this->get('/intercepted/first')->assertBodySame('["first","three","two","one"]');
    }
}
