<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\Core\CompatiblePipelineBuilder;
use Spiral\Core\CoreInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Tests\Core\Unit\Stub\ExceptionInterceptor;
use Spiral\Tests\Core\Unit\Stub\Legacy\LegacyExceptionInterceptor;

final class CompatiblePipelineBuilderTest extends TestCase
{
    public function testCreateEmpty(): void
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn('foo');

        $builder = (new CompatiblePipelineBuilder())->build($handler);
        $result = $builder->handle($this->createPathContext());

        $this->assertSame('foo', $result);
    }

    public function testCreateWithInterceptor(): void
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->never())->method('handle')->willReturn('foo');

        $builder = (new CompatiblePipelineBuilder())->withInterceptors(
            new ExceptionInterceptor(),
        )->build($handler);

        $this->expectException(\RuntimeException::class);
        $builder->handle($this->createPathContext());
    }

    public function testCreateWithLegacyHandler(): void
    {
        $handler = $this->createMock(CoreInterface::class);
        $handler->expects($this->never())->method('callAction')->willReturn('foo');

        $builder = (new CompatiblePipelineBuilder())->withInterceptors(
            new ExceptionInterceptor(),
        )->build($handler);

        $this->expectException(\RuntimeException::class);
        $builder->handle($this->createPathContext());
    }

    public function testCreateWithLegacyInterceptor(): void
    {
        $handler = $this->createMock(CoreInterface::class);
        $handler->expects($this->never())->method('callAction')->willReturn('foo');

        $builder = (new CompatiblePipelineBuilder())->withInterceptors(
            new LegacyExceptionInterceptor(),
        )->build($handler);

        $this->expectException(\RuntimeException::class);
        $builder->handle($this->createPathContext());
    }

    public function testWithMiddleware(): void
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->never())->method('handle')->willReturn('foo');

        $builder = (new CompatiblePipelineBuilder());
        $second = $builder->withInterceptors(new ExceptionInterceptor());

        $this->assertNotSame($builder, $second);
    }

    private function createPathContext(array $path = []): CallContext
    {
        return new CallContext(Target::fromPathArray($path));
    }
}
