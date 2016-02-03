<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Tests\Cases\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Core\Containers\SpiralContainer;
use Spiral\Http\MiddlewarePipeline;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;

class PipelineTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $pipeline = new MiddlewarePipeline([], new SpiralContainer());
        $res = $pipeline->target(function ($req, $res) {
            return $res;
        })->run(new ServerRequest(), new EmptyResponse());

        $this->assertInstanceOf(EmptyResponse::class, $res);
    }

    public function testModify()
    {
        $pipeline = new MiddlewarePipeline([], new SpiralContainer());
        $res = $pipeline->target(function ($req, $res) {
            return $res->withHeader('Abc', 'Test-Value');
        })->run(new ServerRequest(), new EmptyResponse());

        $this->assertInstanceOf(EmptyResponse::class, $res);
        $this->assertEquals('Test-Value', $res->getHeaderLine('Abc'));
    }

    public function testOverwrite()
    {
        $pipeline = new MiddlewarePipeline([], new SpiralContainer());
        $res = $pipeline->target(function ($req, $res) {
            return new HtmlResponse('test');
        })->run(new ServerRequest(), new EmptyResponse());

        $this->assertInstanceOf(HtmlResponse::class, $res);
        $this->assertEquals('test', (string)$res->getBody());
    }

    public function testWriteText()
    {
        $pipeline = new MiddlewarePipeline([], new SpiralContainer());
        $res = $pipeline->target(function ($req, $res) {
            return 'hello world';
        })->run(new ServerRequest(), new Response());

        $this->assertInstanceOf(Response::class, $res);
        $this->assertEquals('hello world', (string)$res->getBody());
    }

    public function testWriteTextAndEcho()
    {
        $pipeline = new MiddlewarePipeline([], new SpiralContainer());
        $res = $pipeline->target(function ($req, $res) {
            echo '-!echo!-';

            return 'hello world';
        })->run(new ServerRequest(), new Response());

        $this->assertInstanceOf(Response::class, $res);
        $this->assertEquals('hello world-!echo!-', (string)$res->getBody());
    }

    public function testEchoAndStatus()
    {
        $pipeline = new MiddlewarePipeline([], new SpiralContainer());
        $res = $pipeline->target(function ($req, $res) {
            echo '-!echo!-';

            return $res->withStatus(400);
        })->run(new ServerRequest(), new Response());

        $this->assertInstanceOf(Response::class, $res);
        $this->assertEquals(400, $res->getStatusCode());
        $this->assertEquals('-!echo!-', (string)$res->getBody());
    }

    public function testWriteJson()
    {
        $pipeline = new MiddlewarePipeline([], new SpiralContainer());
        $res = $pipeline->target(function ($req, $res) {
            return [
                'hello' => 'world'
            ];
        })->run(new ServerRequest(), new Response());

        $this->assertInstanceOf(Response::class, $res);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('application/json', $res->getHeaderLine('Content-Type'));
        $this->assertEquals('{"hello":"world"}', (string)$res->getBody());
    }

    public function testWriteJsonAndStatus()
    {
        $pipeline = new MiddlewarePipeline([], new SpiralContainer());
        $res = $pipeline->target(function ($req, $res) {
            return [
                'status' => 201,
                'hello'  => 'world'
            ];
        })->run(new ServerRequest(), new Response());

        $this->assertInstanceOf(Response::class, $res);
        $this->assertEquals(201, $res->getStatusCode());
        $this->assertEquals('application/json', $res->getHeaderLine('Content-Type'));
        $this->assertEquals('{"status":201,"hello":"world"}', (string)$res->getBody());
    }

    public function testScoping()
    {
        $container = new SpiralContainer();

        $this->assertFalse($container->has(ServerRequestInterface::class));
        $this->assertFalse($container->has(ResponseInterface::class));

        $pipeline = new MiddlewarePipeline([], $container);
        $res = $pipeline->target(function ($req, $res) use ($container) {
            $this->assertTrue($container->has(ServerRequestInterface::class));
            $this->assertTrue($container->has(ResponseInterface::class));

            $this->assertSame($req, $container->get(ServerRequestInterface::class));
            $this->assertSame($res, $container->get(ResponseInterface::class));

            return 'hello world';
        })->run(new ServerRequest(), new Response());

        $this->assertFalse($container->has(ServerRequestInterface::class));
        $this->assertFalse($container->has(ResponseInterface::class));

        $this->assertInstanceOf(Response::class, $res);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('hello world', (string)$res->getBody());
    }

    public function testMiddlewareModifiesResponseAfter()
    {
        $pipeline = new MiddlewarePipeline([], new SpiralContainer());
        $res = $pipeline->target(function ($req, $res) {
            $this->assertFalse($res->hasHeader('Test'));

            return 'hello world';
        })->pushMiddleware(function ($res, $req, $next) {
            return $next($res, $req)->withHeader('Test', 'Value');
        })->run(new ServerRequest(), new Response());

        $this->assertTrue($res->hasHeader('Test'));
        $this->assertEquals('Value', $res->getHeaderLine('Test'));

        $this->assertInstanceOf(Response::class, $res);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('hello world', (string)$res->getBody());
    }
}