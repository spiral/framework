<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Event\InterceptorCalling;
use Spiral\Core\InterceptorPipeline;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;

final class InterceptorPipelineTest extends TestCase
{
    public function testInterceptorCallingEventShouldBeDispatched(): void
    {
        $interceptor = new class implements CoreInterceptorInterface {
            public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
            {
                return null;
            }
        };

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(new InterceptorCalling(
                'test',
                'test2',
                [],
                $interceptor,
                new CallContext(Target::fromPathArray(['test', 'test2'])),
            ));

        $pipeline = new InterceptorPipeline($dispatcher);
        $pipeline->addInterceptor($interceptor);

        $pipeline->withCore(new class implements CoreInterface {
            public function callAction(string $controller, string $action, array $parameters = []): mixed
            {
                return null;
            }
        })->callAction('test', 'test2', []);
    }
}
