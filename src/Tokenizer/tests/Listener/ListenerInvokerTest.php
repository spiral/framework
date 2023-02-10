<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\Listener\ListenerInvoker;
use Spiral\Tests\Tokenizer\Classes\Targets;
use Spiral\Tokenizer\TokenizationListenerInterface;

final class ListenerInvokerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInvoke(): void
    {
        $invoker = new ListenerInvoker();

        $classes = \array_map(
            fn(string $class) => new \ReflectionClass($class),
            [
                Targets\ConsoleCommand::class,
                Targets\Filter::class,
                Targets\ConsoleCommandInterface::class,
                Targets\HomeController::class,
            ],
        );

        $listener = \Mockery::mock(TokenizationListenerInterface::class);

        foreach ($classes as $class) {
            $listener->shouldReceive('listen')
                ->once()
                ->with($class);
        }

        $listener->shouldReceive('finalize')
            ->once();

        $invoker->invoke($listener, $classes);
    }
}
