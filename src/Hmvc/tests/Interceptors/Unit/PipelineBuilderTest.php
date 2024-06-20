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
        $handler = self::createMock(HandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturn('foo');

        $builder = (new PipelineBuilder())->build($handler);
        $result = $builder->handle($this->createPathContext());

        self::assertSame('foo', $result);
    }

    public function testCreateWithMiddleware(): void
    {
        $handler = self::createMock(HandlerInterface::class);
        $handler->expects(self::never())->method('handle')->willReturn('foo');

        $builder = (new PipelineBuilder())->withInterceptors(
            new ExceptionInterceptor(),
        )->build($handler);

        $this->expectException(\RuntimeException::class);
        $builder->handle($this->createPathContext());
    }

    public function testWithMiddleware(): void
    {
        $handler = self::createMock(HandlerInterface::class);
        $handler->expects(self::never())->method('handle')->willReturn('foo');

        $builder = (new PipelineBuilder());
        $second = $builder->withInterceptors(new ExceptionInterceptor());

        self::assertNotSame($builder, $second);
    }

    private function createPathContext(array $path = []): CallContext
    {
        return new CallContext(Target::fromPathArray($path));
    }
}
