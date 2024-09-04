<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Tests\Console\Fixtures\TestCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InterceptorTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public const CONFIG = [
        'interceptors' => [
            'foo'
        ]
    ];

    public function testInterceptorShouldBeResolved(): void
    {
        $this->container->bind('foo', $interceptor = \Mockery::mock(CoreInterceptorInterface::class));

        $interceptor->shouldReceive('process')
            ->once()
            ->withArgs(fn(string $controller, string $action, array $parameters, CoreInterface $core) => $controller === TestCommand::class
                && $action === 'perform'
                && $parameters['input'] instanceof InputInterface
                && $parameters['output'] instanceof OutputInterface
                && $parameters['command'] instanceof TestCommand)
            ->andReturnUsing(fn(string $controller, string $action, array $parameters, CoreInterface $core) => $core->callAction($controller, $action, $parameters));

        $core = $this->getCore($this->getStaticLocator([
            TestCommand::class
        ]));

        $core->run('test');
    }
}
