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
use Spiral\Http\Pipeline;
use Spiral\Router\Exception\RouteException;
use Spiral\Router\PipelineFactory;
use Spiral\Telemetry\NullTracer;

final class PipelineFactoryTest extends \PHPUnit\Framework\TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private ContainerInterface&m\MockInterface $container;
    private FactoryInterface&m\MockInterface $factory;
    private PipelineFactory $pipeline;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = m::mock(ContainerInterface::class);
        $this->factory = m::mock(FactoryInterface::class);

        $this->pipeline = new PipelineFactory($this->container, $this->factory);
    }

    public function testCreatesFromArrayWithPipeline(): void
    {
        $newPipeline = new Pipeline(
            scope: m::mock(ScopeInterface::class)
        );

        $this->assertSame(
            $newPipeline,
            $this->pipeline->createWithMiddleware([$newPipeline])
        );
    }

    public function testCreates(): void
    {
        $container = new Container();

        $this->factory
            ->shouldReceive('make')
            ->once()
            ->with(Pipeline::class)
            ->andReturn($p = new Pipeline(
                $scope = m::mock(ScopeInterface::class),
                tracer: new NullTracer($container)
            ));

        $this->factory
            ->shouldReceive('make')
            ->once()
            ->with('bar', [])
            ->andReturn($middleware5 = m::mock(MiddlewareInterface::class));

        $this->container->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn($middleware4 = m::mock(MiddlewareInterface::class));

        $this->assertSame($p, $this->pipeline->createWithMiddleware([
            'foo',
            $middleware1 = m::mock(MiddlewareInterface::class),
            $middleware2 = m::mock(MiddlewareInterface::class),
            new Autowire('bar'),
        ]));

        $handle = function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return $handler->handle($request);
        };

        $middleware1->shouldReceive('process')->once()->andReturnUsing($handle);
        $middleware2->shouldReceive('process')->once()->andReturnUsing($handle);
        $middleware4->shouldReceive('process')->once()->andReturnUsing($handle);
        $middleware5->shouldReceive('process')->once()->andReturnUsing($handle);

        $scope->shouldReceive('runScope')
            ->once()
            ->andReturn($response = m::mock(ResponseInterface::class));

        $response
            ->shouldReceive('getHeaderLine')->with('Content-Length')->andReturn(['test'])
            ->shouldReceive('getStatusCode')->andReturn(200);

        $p
            ->withHandler(m::mock(RequestHandlerInterface::class))
            ->handle(m::mock(ServerRequestInterface::class));
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
