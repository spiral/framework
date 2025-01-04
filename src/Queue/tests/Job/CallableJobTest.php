<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Job;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Queue\Job\CallableJob;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Tests\Queue\TestCase;

final class CallableJobTest extends TestCase
{
    public function testPayloadCallbackKeyIsRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payload `callback` key is required.');

        $job = new CallableJob(new Container());
        $job->handle('foo', 'foo-id', []);
    }

    public function testPayloadCallbackValueShouldBeClosure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payload `callback` key value type should be a closure.');

        $job = new CallableJob(new Container());
        $job->handle('foo', 'foo-id', ['callback' => 'test']);
    }

    public function testHandle(): void
    {
        $callback = function (string $name, string $id, ContainerInterface $container): void {
            self::assertSame('foo', $name);
            self::assertSame('foo-id', $id);
            self::assertInstanceOf(Container::class, $container);
        };

        $job = new CallableJob(new Container());

        $job->handle('foo', 'foo-id', ['callback' => $callback]);
    }
}
