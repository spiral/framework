<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Interceptors\PipelineBuilder;
use Spiral\Tests\Interceptors\Unit\Stub\ExceptionInterceptor;

final class PipelineBuilderTest extends TestCase
{
    public function testCreateEmpty(): void
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn('foo');

        $builder = (new PipelineBuilder())->build($handler);
        $result = $builder->handle($this->createPathContext());

        $this->assertSame('foo', $result);
    }

    public function testCreateWithMiddleware(): void
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->never())->method('handle')->willReturn('foo');

        $builder = (new PipelineBuilder())->withInterceptors(
            new ExceptionInterceptor(),
        )->build($handler);

        $this->expectException(\RuntimeException::class);
        $builder->handle($this->createPathContext());
    }

    public function testWithMiddleware(): void
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->never())->method('handle')->willReturn('foo');

        $builder = (new PipelineBuilder());
        $second = $builder->withInterceptors(new ExceptionInterceptor());

        $this->assertNotSame($builder, $second);
    }

    private function createPathContext(array $path = []): CallContext
    {
        return new CallContext(Target::fromPathArray($path));
    }
}
