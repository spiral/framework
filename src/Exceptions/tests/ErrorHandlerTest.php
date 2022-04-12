<?php

declare(strict_types=1);

namespace Spiral\Tests\Exceptions;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Spiral\Exceptions\ErrorHandler;
use Spiral\Exceptions\ErrorRendererInterface;
use Spiral\Exceptions\ErrorReporterInterface;

class ErrorHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testDefaultErrorRenderer(): void
    {
        $r1 = m::mock(ErrorRendererInterface::class);
        $r2 = m::mock(ErrorRendererInterface::class);
        $r3 = m::mock(ErrorRendererInterface::class);
        $handler = $this->makeErrorHandler();
        $handler->addRenderer($r1);
        $handler->addRenderer($r2);
        $handler->addRenderer($r3);

        $this->assertSame($r1, $handler->getRenderer());
    }

    public function testErrorHandlerByFormat(): void
    {
        $r0 = m::mock(ErrorRendererInterface::class);
        $r1 = m::mock(ErrorRendererInterface::class);
        $r1->shouldReceive('canRender')->withArgs(['test'])->andReturnTrue();
        $r2 = m::mock(ErrorRendererInterface::class);
        $r2->shouldReceive('canRender')->withArgs(['test'])->andReturnFalse();
        $r3 = m::mock(ErrorRendererInterface::class);
        $r3->shouldReceive('canRender')->withArgs(['test'])->andReturnFalse();
        $handler = $this->makeErrorHandler();
        $handler->addRenderer($r0);
        $handler->addRenderer($r1);
        $handler->addRenderer($r2);
        $handler->addRenderer($r3);

        $this->assertSame($r1, $handler->getRenderer('test'));
    }

    public function testAllReportersShouldBeCalled(): void
    {
        $exception = new \Exception();

        $r1 = m::mock(ErrorReporterInterface::class);
        $r1->shouldReceive('report')->withArgs([$exception])->once();
        $r2 = m::mock(ErrorReporterInterface::class);
        $r2->shouldReceive('report')->withArgs([$exception])->once();
        $r3 = m::mock(ErrorReporterInterface::class);
        $r3->shouldReceive('report')->withArgs([$exception])->once()->andThrows(new RuntimeException());
        $r4 = m::mock(ErrorReporterInterface::class);
        $r4->shouldReceive('report')->withArgs([$exception])->once();
        $handler = $this->makeErrorHandler();
        $handler->addReporter($r1);
        $handler->addReporter($r2);
        $handler->addReporter($r3);
        $handler->addReporter($r4);

        $handler->report($exception);
        $this->assertTrue(true);
    }

    private function makeErrorHandler(): ErrorHandler
    {
        return new ErrorHandler();
    }
}
