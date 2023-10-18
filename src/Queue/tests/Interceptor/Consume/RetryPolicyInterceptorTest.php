<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor\Consume;

use Spiral\Attributes\ReaderInterface;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Attribute\RetryPolicy;
use Spiral\Queue\Exception\JobException;
use Spiral\Queue\Exception\RetryException;
use Spiral\Queue\Interceptor\Consume\RetryPolicyInterceptor;
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
        $this->interceptor = new RetryPolicyInterceptor($this->reader);
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

    public function testWithoutRetryPolicy(): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(null);

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with(self::class, 'bar', [])
            ->willThrowException(new \Exception('Something went wrong'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Something went wrong');
        $this->interceptor->process(self::class, 'bar', [], $this->core);
    }

    public function testNotRetryableException(): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(new RetryPolicy());

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with(self::class, 'bar', [])
            ->willThrowException(new \Exception('Something went wrong'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Something went wrong');
        $this->interceptor->process(self::class, 'bar', [], $this->core);
    }

    public function testWithDefaultRetryPolicy(): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(new RetryPolicy());

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with(self::class, 'bar', [])
            ->willThrowException(new TestRetryException());

        try {
            $this->interceptor->process(self::class, 'bar', [], $this->core);
        } catch (RetryException $e) {
            $this->assertSame(1, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['1']], $e->getOptions()->getHeaders());
        }
    }

    public function testWithoutRetryPolicyAttribute(): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(null);

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with(self::class, 'bar', [])
            ->willThrowException(new TestRetryException(
                retryPolicy: new \Spiral\Queue\RetryPolicy(maxAttempts: 2, delay: 4)
            ));

        try {
            $this->interceptor->process(self::class, 'bar', [], $this->core);
        } catch (RetryException $e) {
            $this->assertSame(4, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['1']], $e->getOptions()->getHeaders());
        }
    }

    public function testWithRetryPolicyInAttribute(): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(
            new RetryPolicy(maxAttempts: 3, delay: 4, multiplier: 2)
        );

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with(self::class, 'bar', ['headers' => ['attempts' => ['1']]])
            ->willThrowException(new TestRetryException());

        try {
            $this->interceptor->process(
                self::class,
                'bar',
                ['headers' => ['attempts' => ['1']]],
                $this->core
            );
        } catch (RetryException $e) {
            $this->assertSame(8, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['2']], $e->getOptions()->getHeaders());
        }
    }

    public function testWithRetryPolicyInException(): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(
            new RetryPolicy(maxAttempts: 30, delay: 400, multiplier: 25)
        );

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with(self::class, 'bar', ['headers' => ['attempts' => ['1']]])
            ->willThrowException(new TestRetryException(
                retryPolicy: new \Spiral\Queue\RetryPolicy(maxAttempts: 3, delay: 4, multiplier: 2)
            ));

        try {
            $this->interceptor->process(
                self::class,
                'bar',
                ['headers' => ['attempts' => ['1']]],
                $this->core
            );
        } catch (RetryException $e) {
            $this->assertSame(8, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['2']], $e->getOptions()->getHeaders());
        }
    }

    public function testWithRetryPolicyInExceptionInsideJobException(): void
    {
        $this->reader->expects($this->once())->method('firstClassMetadata')->willReturn(
            new RetryPolicy(maxAttempts: 30, delay: 400, multiplier: 25)
        );

        $this->core
            ->expects($this->once())
            ->method('callAction')
            ->with(self::class, 'bar', ['headers' => ['attempts' => ['1']]])
            ->willThrowException(new JobException(
                previous: new TestRetryException(
                    retryPolicy: new \Spiral\Queue\RetryPolicy(maxAttempts: 3, delay: 4, multiplier: 2)
                )
            ));

        try {
            $this->interceptor->process(
                self::class,
                'bar',
                ['headers' => ['attempts' => ['1']]],
                $this->core
            );
        } catch (RetryException $e) {
            $this->assertSame(8, $e->getOptions()->getDelay());
            $this->assertSame(['attempts' => ['2']], $e->getOptions()->getHeaders());
        }
    }
}
