<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Middleware;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Spiral\Boot\Environment\DebugMode;
use Spiral\Exceptions\ExceptionHandlerInterface;
use Spiral\Exceptions\ExceptionRendererInterface;
use Spiral\Http\ErrorHandler\RendererInterface;
use Spiral\Http\Exception\ClientException;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;
use Spiral\Http\Middleware\ErrorHandlerMiddleware\EnvSuppressErrors;
use Spiral\Router\Exception\RouterException;

final class ErrorHandlerMiddlewareTest extends TestCase
{
    private RequestHandlerInterface $handler;
    private ServerRequestInterface $request;
    private ExceptionHandlerInterface $exceptionHandler;
    private LoggerInterface $logger;
    private RendererInterface $renderer;

    protected function setUp(): void
    {
        $this->request = new ServerRequest('GET', '/');
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->exceptionHandler = $this->createMock(ExceptionHandlerInterface::class);
        $this->renderer = $this->createMock(RendererInterface::class);
    }

    /**
     * @dataProvider exceptionsDataProvider
     */
    public function testHandleExceptionWithDebugFalse(\Throwable $e, int $code): void
    {
        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->with($this->request)
            ->willThrowException($e);

        $this->exceptionHandler
            ->expects($this->once())
            ->method('report')
            ->with($e);

        $this->logger
            ->expects($this->once())
            ->method('error');

        $this->renderer
            ->expects($this->once())
            ->method('renderException')
            ->with($this->request, $code, $e);

        $this->exceptionHandler
            ->expects($this->never())
            ->method('getRenderer');

        $middleware = new ErrorHandlerMiddleware(
            new EnvSuppressErrors(DebugMode::Disabled),
            $this->renderer,
            new Psr17Factory(),
            $this->exceptionHandler
        );
        $middleware->setLogger($this->logger);

        $middleware->process($this->request, $this->handler);
    }

    /**
     * @dataProvider exceptionsDataProvider
     */
    public function testHandleExceptionWithDebugTrue(\Throwable $e, int $code): void
    {
        $renderer = $this->createMock(ExceptionRendererInterface::class);
        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($e);

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->with($this->request)
            ->willThrowException($e);

        $this->exceptionHandler
            ->expects($this->once())
            ->method('report')
            ->with($e);

        $this->exceptionHandler
            ->expects($this->once())
            ->method('getRenderer')
            ->willReturn($renderer);

        $this->logger
            ->expects($this->never())
            ->method('error');

        $this->renderer
            ->expects($this->never())
            ->method('renderException');

        $middleware = new ErrorHandlerMiddleware(
            new EnvSuppressErrors(DebugMode::Enabled),
            $this->renderer,
            new Psr17Factory(),
            $this->exceptionHandler
        );
        $middleware->setLogger($this->logger);

        $response = $middleware->process($this->request, $this->handler);

        $this->assertSame($code, $response->getStatusCode());
    }

    public function exceptionsDataProvider(): \Traversable
    {
        yield [new ClientException(message: 'some error'), 400];
        yield [new RouterException(message: 'some error'), 404];
        yield [new \Error('some error', 555), 500];
        yield [new \Error('some error'), 500];
    }
}
