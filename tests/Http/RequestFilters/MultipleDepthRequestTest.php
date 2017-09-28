<?php

namespace Spiral\Tests\Http\RequestFilters;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Tests\Http\Fixtures\DemoRequest;
use Spiral\Tests\Http\Fixtures\FirstDepthRequest;
use Spiral\Tests\Http\Fixtures\ThirdDepthRequest;
use Spiral\Tests\Http\HttpTest;
use Zend\Diactoros\ServerRequest;

class MultipleDepthRequestTest extends HttpTest
{
    public function testHasErrors()
    {
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody([]);
        $this->container->bind(ServerRequestInterface::class, $serverRequest);

        /** @var DemoRequest $request */
        $request = $this->container->get(FirstDepthRequest::class);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('first', $request->getErrors());
        $this->assertArrayHasKey('second', $request->getErrors()['first']);
        $this->assertArrayHasKey('third', $request->getErrors()['first']['second']);
    }

    public function testDataPassed()
    {
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody([
            'first' => [
                'second' => [
                    'third' => '3rd value',
                ],
            ]
        ]);
        $this->container->bind(ServerRequestInterface::class, $serverRequest);

        /** @var DemoRequest $request */
        $request = $this->container->get(FirstDepthRequest::class);

        $this->assertTrue($request->isValid());
        $this->assertArrayNotHasKey('first', $request->getErrors());
        $this->assertArrayNotHasKey('second', $request->getErrors()['first']);
        $this->assertArrayNotHasKey('third', $request->getErrors()['first']['second']);

        $this->assertArrayHasKey('first', $request->getValidator()->getData());
        $this->assertArrayHasKey('second', $request->getValidator()->getData()['first']);
        $this->assertArrayHasKey('third', $request->getValidator()->getData()['first']['second']);
        $this->assertSame('3rd value', $request->getValidator()->getData()['first']['second']['third']);
    }
}
