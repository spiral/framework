<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Http\CurrentRequest;
use Spiral\Http\Pipeline;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\PipelineFactory;
use Spiral\Telemetry\NullTracer;

final class PipelineFactoryTest extends \PHPUnit\Framework\TestCase
{
    private ContainerInterface $container;
    private FactoryInterface $factory;
    private PipelineFactory $pipeline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = m::mock(FactoryInterface::class);

        $this->pipeline = new PipelineFactory($this->container, $this->factory);
    }

    public function testCreatesFromArrayWithPipeline(): void
    {
        $newPipeline = new Pipeline(
            scope: $this->createMock(ScopeInterface::class),
        );

        $this->assertSame(
            $newPipeline,
            $this->pipeline->createWithMiddleware([$newPipeline])
        );
    }

    public function testCreates(): void
    {
        $container = new Container();
        $container->bindSingleton(CurrentRequest::class, new CurrentRequest());

        $this->factory
            ->shouldReceive('make')
            ->once()
            ->with(Pipeline::class)
            ->andReturn($p = new Pipeline(
                $this->createMock(ScopeInterface::class),
                tracer: new NullTracer($container)
            ));

        $this->factory
            ->shouldReceive('make')
            ->once()
            ->with('bar', [])
            ->andReturn($middleware5 = $this->createMock(MiddlewareInterface::class));

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn($middleware4 = $this->createMock(MiddlewareInterface::class));

        $this->assertSame($p, $this->pipeline->createWithMiddleware([
            'foo',
            $middleware1 = $this->createMock(MiddlewareInterface::class),
            $middleware2 = $this->createMock(MiddlewareInterface::class),
            new Autowire('bar'),
        ]));

        $handle = function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return $handler->handle($request);
        };

        $middleware1->expects($this->once())->method('process')->willReturnCallback($handle);
        $middleware2->expects($this->once())->method('process')->willReturnCallback($handle);
        $middleware4->expects($this->once())->method('process')->willReturnCallback($handle);
        $middleware5->expects($this->once())->method('process')->willReturnCallback($handle);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->exactly(4))->method('getHeaderLine')->with('Content-Length')->willReturn('test');
        $response->expects($this->exactly(8))->method('getStatusCode')->willReturn(200);

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $p
            ->withHandler($requestHandler)
            ->handle($this->createMock(ServerRequestInterface::class));
    }

    #[DataProvider('invalidTypeDataProvider')]
    public function testInvalidTypeShouldThrowAnException(mixed $value, string $type): void
    {
        $this->factory
            ->shouldReceive('make')
            ->once()
            ->with(Pipeline::class)
            ->andReturn(new Pipeline(m::mock(ScopeInterface::class)));

        $this->expectException(RouteException::class);
        $this->expectExceptionMessage(\sprintf('Invalid middleware `%s`', $type));
        $this->pipeline->createWithMiddleware([$value]);
    }

    public static function invalidTypeDataProvider(): \Generator
    {
        yield 'true' => [true, 'bool'];
        yield 'false' => [false, 'bool'];
        yield 'array' => [[], 'array'];
        yield 'int' => [1, 'int'];
        yield 'float' => [1.0, 'float'];
        yield 'object' => [new \stdClass(), 'stdClass'];
    }
}
