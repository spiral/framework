<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor\Push;

use Mockery as m;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Queue\Interceptor\Push\Core;
use Spiral\Queue\Options;
use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Telemetry\TracerInterface;
use Spiral\Tests\Queue\TestCase;

final class CoreTest extends TestCase
{
    public function testCallActionWithNullOptions(): void
    {
        $core = new Core(
            $queue = m::mock(QueueInterface::class)
        );
        $queue->shouldReceive('push')->once()
            ->withArgs(function (string $name, array $payload = [], OptionsInterface $options = null) {
                return $name === 'foo' && $payload === ['baz' => 'baf'] && $options instanceof Options;
            });

        $core->callAction('foo', 'bar', [
            'id' => 'job-id',
            'payload' => ['baz' => 'baf'],
            'options' => null,
        ]);
    }

    public function testCallActionWithOptions(): void
    {
        $core = new Core(
            $queue = m::mock(QueueInterface::class)
        );

        $options = new Options();

        $queue->shouldReceive('push')->once()
            ->with('foo', ['baz' => 'baf'], $options);

        $core->callAction('foo', 'bar', [
            'id' => 'job-id',
            'payload' => ['baz' => 'baf'],
            'options' => $options,
        ]);
    }

    public function testCallWithTracerContext(): void
    {
        $core = new Core(
            $queue = m::mock(QueueInterface::class),
        );

        $container = new Container();
        $container->bind(TracerInterface::class, $tracer = m::mock(TracerInterface::class));


        $tracer->shouldReceive('getContext')->once()->andReturn(['foo' => ['bar']]);
        $tracer->shouldReceive('trace')->once()->andReturnUsing(function ($name, $callback) {
            return $callback();
        });

        $queue->shouldReceive('push')->once()
            ->withArgs(function (string $name, array $payload = [], OptionsInterface $options = null) {
                return $name === 'foo'
                    && $payload === ['baz' => 'baf']
                    && $options->getHeader('foo') === ['bar'];
            });

        ContainerScope::runScope($container, function() use($core) {
            $core->callAction('foo', 'bar', [
                'id' => 'job-id',
                'payload' => ['baz' => 'baf'],
                'options' => null,
            ]);
        });
    }

    public function testCallWithTracerContextWitoutOptionsWithHeadersSupport(): void
    {
        $core = new Core(
            $queue = m::mock(QueueInterface::class),
            $tracer = m::mock(TracerInterface::class),
        );

        $tracer->shouldNotReceive('getContext');

        $queue->shouldReceive('push')->once()
            ->withArgs(function (string $name, array $payload = [], OptionsInterface $options = null) {
                return $name === 'foo'
                    && $payload === ['baz' => 'baf']
                    && $options !== null;
            });

        $core->callAction('foo', 'bar', [
            'id' => 'job-id',
            'payload' => ['baz' => 'baf'],
            'options' => m::mock(OptionsInterface::class),
        ]);
    }
}
