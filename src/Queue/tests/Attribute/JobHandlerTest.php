<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Factory;
use Spiral\Queue\Attribute\JobHandler;
use Spiral\Tests\Queue\Attribute\Stub\ExtendedJobHandler;
use Spiral\Tests\Queue\Attribute\Stub\JobHandlerAttribute;
use Spiral\Tests\Queue\Attribute\Stub\WithExtendedJobHandlerAttribute;
use Spiral\Tests\Queue\Attribute\Stub\WithoutJobHandler;

final class JobHandlerTest extends TestCase
{
    #[DataProvider('classesProvider')]
    public function testJobHandler(string $class, ?JobHandler $expected): void
    {
        $reader = (new Factory())->create();

        self::assertEquals($expected, $reader->firstClassMetadata(new \ReflectionClass($class), JobHandler::class));
    }

    public static function classesProvider(): \Traversable
    {
        yield [WithoutJobHandler::class, null];
        yield [JobHandlerAttribute::class, new JobHandler('test')];
        yield [WithExtendedJobHandlerAttribute::class, new ExtendedJobHandler()];
    }
}
