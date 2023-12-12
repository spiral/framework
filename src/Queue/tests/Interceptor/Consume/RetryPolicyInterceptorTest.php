<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor\Consume;

use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Queue\Attribute\RetryPolicy;
use Spiral\Queue\Exception\JobException;
use Spiral\Queue\Exception\RetryException;
use Spiral\Queue\HandlerInterface;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\Interceptor\Consume\RetryPolicyInterceptor;
use Spiral\Queue\QueueRegistry;
use Spiral\Queue\RetryPolicyInterface;
use Spiral\Tests\Queue\Exception\TestRetryException;
use Spiral\Tests\Queue\TestCase;

final class RetryPolicyInterceptorTest extends TestCase
{
    private ReaderInterface $reader;
    private CoreInterface $core;
    private RetryPolicyInterceptor $interceptor;

    protected function setUp(): void
    {
        $this->reader = $this->createMock(ReaderInterface::class);
        $this->core = $this->createMock(CoreInterface::class);

        $registry = new QueueRegistry(
            $this->createMock(ContainerInterface::class),
            $this->createMock(FactoryInterface::class),
            $this->createMock(HandlerRegistryInterface::class),
        );
        $registry->setHandler('foo', $this->createMock(HandlerInterface::class));

        $this->interceptor = new RetryPolicyInterceptor($this->reader, $registry);
    }

    public function testWithoutException(): void
    {
        $this->reader->expects($this->never())->method('firstClassMetadata');

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with('foo', 'bar', [])
            ->willReturn('result');

        $this->assertSame('result', $this->interceptor->process('foo', 'bar', [], $this->core));
    }

    #[DataProvider('jobNameDataProvider')]
    public function testWithoutRetryPolicy(string $name): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(null);

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with($name, 'bar', [])
            ->willThrowException(new \Exception('Something went wrong'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Something went wrong');
        $this->interceptor->process($name, 'bar', [], $this->core);
    }

    #[DataProvider('jobNameDataProvider')]
    public function testNotRetryableException(string $name): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(new RetryPolicy());

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with($name, 'bar', [])
            ->willThrowException(new \Exception('Something went wrong'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Something went wrong');
        $this->interceptor->process($name, 'bar', [], $this->core);
    }

    #[DataProvider('jobNameDataProvider')]
    public function testWithDefaultRetryPolicy(string $name): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(new RetryPolicy());

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with($name, 'bar', [])
            ->willThrowException(new TestRetryException());

        try {
            $this->interceptor->process($name, 'bar', [], $this->core);
        } catch (RetryException $e) {
            $this->assertSame(1, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['1']], $e->getOptions()->getHeaders());
        }
    }

    #[DataProvider('jobNameDataProvider')]
    public function testWithoutRetryPolicyAttribute(string $name): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(null);

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with($name, 'bar', [])
            ->willThrowException(new TestRetryException(
                retryPolicy: new \Spiral\Queue\RetryPolicy(maxAttempts: 2, delay: 4)
            ));

        try {
            $this->interceptor->process($name, 'bar', [], $this->core);
        } catch (RetryException $e) {
            $this->assertSame(4, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['1']], $e->getOptions()->getHeaders());
        }
    }

    #[DataProvider('jobNameDataProvider')]
    public function testWithRetryPolicyInAttribute(string $name): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(
            new RetryPolicy(maxAttempts: 3, delay: 4, multiplier: 2)
        );

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with($name, 'bar', ['headers' => ['attempts' => ['1']]])
            ->willThrowException(new TestRetryException());

        try {
            $this->interceptor->process(
                $name,
                'bar',
                ['headers' => ['attempts' => ['1']]],
                $this->core
            );
        } catch (RetryException $e) {
            $this->assertSame(8, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['2']], $e->getOptions()->getHeaders());
        }
    }

    #[DataProvider('jobNameDataProvider')]
    public function testWithRetryPolicyInException(string $name): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(
            new RetryPolicy(maxAttempts: 30, delay: 400, multiplier: 25)
        );

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with($name, 'bar', ['headers' => ['attempts' => ['1']]])
            ->willThrowException(new TestRetryException(
                retryPolicy: new \Spiral\Queue\RetryPolicy(maxAttempts: 3, delay: 4, multiplier: 2)
            ));

        try {
            $this->interceptor->process(
                $name,
                'bar',
                ['headers' => ['attempts' => ['1']]],
                $this->core
            );
        } catch (RetryException $e) {
            $this->assertSame(8, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['2']], $e->getOptions()->getHeaders());
        }
    }

    #[DataProvider('jobNameDataProvider')]
    public function testWithCustomRetryPolicyInException(string $name): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(
            new RetryPolicy(maxAttempts: 30, delay: 400, multiplier: 25)
        );

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with($name, 'bar', ['headers' => ['attempts' => ['1']]])
            ->willThrowException(new TestRetryException(
                retryPolicy: new class implements RetryPolicyInterface {
                    public function isRetryable(\Throwable $exception, int $attempts = 0): bool
                    {
                        return true;
                    }

                    public function getDelay(int $attempts = 0): int
                    {
                        return 5;
                    }
                }
            ));

        try {
            $this->interceptor->process(
                $name,
                'bar',
                ['headers' => ['attempts' => ['1']]],
                $this->core
            );
        } catch (RetryException $e) {
            $this->assertSame(5, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['2']], $e->getOptions()->getHeaders());
        }
    }

    #[DataProvider('jobNameDataProvider')]
    public function testWithRetryPolicyInExceptionInsideJobException(string $name): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(
            new RetryPolicy(maxAttempts: 30, delay: 400, multiplier: 25)
        );

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with($name, 'bar', ['headers' => ['attempts' => ['1']]])
            ->willThrowException(new JobException(
                previous: new TestRetryException(
                    retryPolicy: new \Spiral\Queue\RetryPolicy(maxAttempts: 3, delay: 4, multiplier: 2)
                )
            ));

        try {
            $this->interceptor->process(
                $name,
                'bar',
                ['headers' => ['attempts' => ['1']]],
                $this->core
            );
        } catch (RetryException $e) {
            $this->assertSame(8, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['2']], $e->getOptions()->getHeaders());
        }
    }

    public static function jobNameDataProvider(): \Traversable
    {
        yield [self::class];
        yield ['foo'];
    }
}
