<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Interceptor;

use Spiral\Tests\Framework\HttpTest;

class PipelineInterceptorTest extends HttpTest
{
    public function testWithoutPipeline(): void
    {
        $response = $this->get('/intercepted/without')->getBody();
        $output = json_decode((string)$response, true, 512, JSON_THROW_ON_ERROR);
        //appends are executed after the sub-core action called: one->two->three->action[without]->[three]->[two]->[one]
        $this->assertSame(['without', 'three', 'two', 'one'], $output);
    }

    public function testWith(): void
    {
        $response = $this->get('/intercepted/with')->getBody();
        $output = json_decode((string)$response, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(['with', 'three', 'two', 'one'], $output);
    }

    public function testMix(): void
    {
        $response = $this->get('/intercepted/mix')->getBody();
        $output = json_decode((string)$response, true, 512, JSON_THROW_ON_ERROR);
        //pipeline interceptors are injected into the middle
        $this->assertSame(['mix', 'six', 'three', 'two', 'one', 'five', 'four'], $output);
    }

    public function testDup(): void
    {
        $response = $this->get('/intercepted/dup')->getBody();
        $output = json_decode((string)$response, true, 512, JSON_THROW_ON_ERROR);
        //pipeline interceptors are added to the end
        $this->assertSame(['dup', 'three', 'two', 'one', 'three', 'two', 'one'], $output);
    }

    public function testSkipNext(): void
    {
        $response = $this->get('/intercepted/skip')->getBody();
        $output = json_decode((string)$response, true, 512, JSON_THROW_ON_ERROR);
        //interceptors after current pipeline are ignored
        $this->assertSame(['skip', 'three', 'two', 'one', 'one'], $output);
    }

    public function testSkipIfFirst(): void
    {
        $response = $this->get('/intercepted/first')->getBody();
        $output = json_decode((string)$response, true, 512, JSON_THROW_ON_ERROR);
        //interceptors after current pipeline are ignored
        $this->assertSame(['first', 'three', 'two', 'one'], $output);
    }
}
