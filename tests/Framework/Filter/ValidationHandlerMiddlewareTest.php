<?php

declare(strict_types=1);

namespace Framework\Filter;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Filter\JsonErrorsRenderer;
use Spiral\Filter\ValidationHandlerMiddleware;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\ErrorsRendererInterface;

final class ValidationHandlerMiddlewareTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private ValidationHandlerMiddleware $middleware;
    private ErrorsRendererInterface|m\MockInterface $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $container = m::mock(ContainerInterface::class);
        $this->renderer = m::mock(ErrorsRendererInterface::class);

        $this->middleware = new ValidationHandlerMiddleware(
            $container, $this->renderer
        );
    }

    public function testDefaultRendererShouldBeUsed(): void
    {
        $container = m::mock(ContainerInterface::class);

        $container->shouldReceive('get')
            ->once()
            ->with(JsonErrorsRenderer::class)
            ->andReturn($this->renderer);

        new ValidationHandlerMiddleware($container);
    }

    public function testRequestWithoutValidationExceptionShouldBeProcessed(): void
    {
        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $handler->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($response = m::mock(ResponseInterface::class));

        $this->assertSame(
            $response,
            $this->middleware->process($request, $handler)
        );
    }

    public function testRequestWithNonValidationExceptionShouldThrowIt(): void
    {
        $this->expectException(\Exception::class);

        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $handler->shouldReceive('handle')
            ->andThrow(new \Exception('something went wrong'));


        $this->middleware->process($request, $handler);
    }

    public function testRequestWithValidationExceptionShouldBeProcessedByRenderer(): void
    {
        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $handler->shouldReceive('handle')
            ->andThrow(new ValidationException($errors = [
                'foo' => 'bar'
            ], $context = 'foo_context'));

        $this->renderer->shouldReceive('render')
            ->once()
            ->with($errors, $context)
            ->andReturn($response = m::mock(ResponseInterface::class));

        $this->assertSame(
            $response,
            $this->middleware->process($request, $handler)
        );
    }
}
