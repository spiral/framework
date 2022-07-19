<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Interceptor;

use Laminas\Diactoros\Response\JsonResponse;
use Mockery as m;
use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Domain\FilterInterceptor;
use Spiral\Filters\ArrayInput;
use Spiral\Tests\Filters\BaseTest;
use Spiral\Tests\Filters\Fixtures\MessageFilter;
use Spiral\Tests\Filters\Fixtures\SelfErrorsRenderingFilter;

final class FilterInterceptorTest extends BaseTest
{
    public function testInterceptorRenderInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(MessageFilter::class, new ArrayInput([]));

        $core = m::mock(CoreInterface::class);
        $core->shouldReceive('callAction')
            ->once()
            ->with(MessageFilterAction::class, '__invoke', [$filter]);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with(MessageFilter::class)
            ->andReturn($filter);

        $interceptor = new FilterInterceptor($container);

        $response = $interceptor->process(MessageFilterAction::class, '__invoke', [$filter], $core);
        self::assertEquals(['status' => 400, 'errors' => ['id' => 'ID is not valid.']], $response);
    }

    public function testFilterRenderInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(SelfErrorsRenderingFilter::class, new ArrayInput([]));

        $core = m::mock(CoreInterface::class);
        $core->shouldReceive('callAction')
            ->once()
            ->with(SelfErrorsRenderingFilterAction::class, '__invoke', [$filter]);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with(SelfErrorsRenderingFilter::class)
            ->andReturn($filter);

        $interceptor = new FilterInterceptor($container);

        $response = $interceptor->process(SelfErrorsRenderingFilterAction::class, '__invoke', [$filter], $core);

        self::assertEquals([
            'success' => false,
            'errors' => [
                'id' => 'ID is not valid.'
            ],
            'requestParams' => [],
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
    public function __invoke(SelfErrorsRenderingFilter $filter): JsonResponse
    {
        return new JsonResponse([]);
    }
}
