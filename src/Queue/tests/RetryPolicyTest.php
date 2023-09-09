<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Queue\RetryPolicy;
use Spiral\Tests\Queue\Exception\TestRetryException;

final class RetryPolicyTest extends TestCase
{
    public function testInvalidMaxAttempts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum attempts must be greater than or equal to zero: `-1` given.');
        new RetryPolicy(-1, 0);
    }

    public function testInvalidDelay(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Delay must be greater than or equal to zero: `-1` given.');
        new RetryPolicy(1, -1);
    }

    public function testInvalidMultiplier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiplier must be greater than zero: `-1` given.');
        new RetryPolicy(1, 1, -1);
    }

    #[DataProvider('retryableDataProvider')]
    public function testIsRetryable(\Throwable $exception, int $attempts, bool $expected): void
    {
        $policy = new RetryPolicy(1, 0);

        $this->assertSame($expected, $policy->isRetryable($exception, $attempts));
    }

    public function testGetDelayWithoutMultiplier(): void
    {
        $policy = new RetryPolicy(4, 1_000);

        $this->assertSame(1_000, $policy->getDelay());
        $this->assertSame(1_000, $policy->getDelay(1));
        $this->assertSame(1_000, $policy->getDelay(2));
        $this->assertSame(1_000, $policy->getDelay(3));
        $this->assertSame(1_000, $policy->getDelay(4));
    }

    public function testGetDelayWithMultiplier(): void
    {
        $policy = new RetryPolicy(4, 1_000, 2);

        $this->assertSame(1_000, $policy->getDelay());
        $this->assertSame(2_000, $policy->getDelay(1));
        $this->assertSame(4_000, $policy->getDelay(2));
        $this->assertSame(8_000, $policy->getDelay(3));
        $this->assertSame(16_000, $policy->getDelay(4));
    }

    public static function retryableDataProvider(): \Traversable
    {
        yield [new \DomainException(), 0, false];
        yield [new \DomainException(), 1, false];
        yield [new TestRetryException(), 0, true];
        yield [new TestRetryException(), 1, false];
        yield [new TestRetryException(false), 0, false];
        yield [new TestRetryException(false), 1, false];
    }
}
