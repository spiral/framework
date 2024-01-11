<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Factory;
use Spiral\Queue\Attribute\RetryPolicy;
use Spiral\Tests\Queue\Attribute\Stub\ExtendedRetryPolicy;
use Spiral\Tests\Queue\Attribute\Stub\WithDefaultRetryPolicyAttribute;
use Spiral\Tests\Queue\Attribute\Stub\WithExtendedRetryPolicyAttribute;
use Spiral\Tests\Queue\Attribute\Stub\WithoutRetryPolicy;
use Spiral\Tests\Queue\Attribute\Stub\WithRetryPolicyAttribute;

final class RetryPolicyTest extends TestCase
{
    #[DataProvider('classesProvider')]
    public function testRetryPolicy(string $class, ?RetryPolicy $expected): void
    {
        $reader = (new Factory())->create();

        $this->assertEquals($expected, $reader->firstClassMetadata(new \ReflectionClass($class), RetryPolicy::class));
    }

    public static function classesProvider(): \Traversable
    {
        yield [WithoutRetryPolicy::class, null];
        yield [WithDefaultRetryPolicyAttribute::class, new RetryPolicy()];
        yield [WithRetryPolicyAttribute::class, new RetryPolicy(5, 3_000, 2.5)];
        yield [WithExtendedRetryPolicyAttribute::class, new ExtendedRetryPolicy()];
    }
}
