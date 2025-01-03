<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Job;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Queue\Job\ObjectJob;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Tests\Queue\TestCase;

final class ObjectJobTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }

    public function testPayloadObjectKeyIsRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payload `object` key is required.');

        $job = new ObjectJob($this->container);
        $job->handle('foo', 'foo-id', []);
    }

    public function testPayloadObjectValueShouldBeObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payload `object` key value type should be an object.');

        $job = new ObjectJob($this->container);
        $job->handle('foo', 'foo-id', ['object' => 'test']);
    }

    public function testHandleWithHandleMethod(): void
    {
        $object = new class($this) {
            public function __construct(private $testCase)
            {
            }

            public function handle(string $name, string $id, ContainerInterface $container): void
            {
                $this->testCase->assertSame('foo', $name);
                $this->testCase->assertSame('foo-id', $id);
                $this->testCase->assertInstanceOf(Container::class, $container);
            }
        };

        $job = new ObjectJob($this->container);

        $job->handle('foo', 'foo-id', ['object' => $object]);
    }

    public function testHandleWithInvokeMethod(): void
    {
        $object = new class($this) {
            public function __construct(private $testCase)
            {
            }

            public function __invoke(string $name, string $id, ContainerInterface $container): void
            {
                $this->testCase->assertSame('foo', $name);
                $this->testCase->assertSame('foo-id', $id);
                $this->testCase->assertInstanceOf(Container::class, $container);
            }
        };

        $job = new ObjectJob($this->container);

        $job->handle('foo', 'foo-id', ['object' => $object]);
    }
}
