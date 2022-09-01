<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Interceptor;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Container\ContainerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\CoreInterface;
use Spiral\Domain\FilterWithAttributeInterceptor;
use Spiral\Filters\ArrayInput;
use Spiral\Filters\RenderWith;
use Spiral\Tests\Filters\BaseTest;
use Spiral\Tests\Filters\Fixtures\MessageFilter;
use Spiral\Tests\Filters\Fixtures\FilterWithErrorsRenderer;
use Spiral\Tests\Filters\Fixtures\TestErrorsInterfaceRenderer;

final class FilterInterceptorTest extends BaseTest
{
    public function testInterceptorRenderInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(MessageFilter::class, new ArrayInput([]));

        $core = $this->createMock(CoreInterface::class);
        $core->method('callAction')->with(MessageFilterAction::class, '__invoke', [$filter]);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->with(MessageFilter::class)
            ->willReturn($filter);

        $reader = $this->createMock(ReaderInterface::class);
        $reader->method('firstClassMetadata')
            ->willReturn(null);

        $interceptor = new FilterWithAttributeInterceptor($container, $reader);

        $response = $interceptor->process(MessageFilterAction::class, '__invoke', [$filter], $core);
        self::assertEquals(['status' => 400, 'errors' => ['id' => 'ID is not valid.']], $response);
    }

    public function testFilterRenderInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(FilterWithErrorsRenderer::class, new ArrayInput([]));

        $core = $this->createMock(CoreInterface::class);
        $core->method('callAction')
            ->with(SelfErrorsRenderingFilterAction::class, '__invoke', [$filter]);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->withConsecutive(
                [TestErrorsInterfaceRenderer::class],
                [FilterWithErrorsRenderer::class],
            )
            ->willReturnOnConsecutiveCalls(new TestErrorsInterfaceRenderer(), $filter);

        $reader = $this->createMock(ReaderInterface::class);
        $reader->method('firstClassMetadata')
            ->willReturn(new RenderWith(TestErrorsInterfaceRenderer::class));

        $interceptor = new FilterWithAttributeInterceptor($container, $reader);

        $response = $interceptor->process(SelfErrorsRenderingFilterAction::class, '__invoke', [$filter], $core);

        self::assertEquals([
            'success' => false,
            'errors' => [
                'id' => 'ID is not valid.'
            ],
        ], $response);
    }
}

final class MessageFilterAction
{
    public function __invoke(MessageFilter $filter): JsonResponse
    {
        return new JsonResponse([]);
    }
}

final class SelfErrorsRenderingFilterAction
{
    public function __invoke(FilterWithErrorsRenderer $filter): JsonResponse
    {
        return new JsonResponse([]);
    }
}
