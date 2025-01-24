<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Factory;
use Spiral\Queue\Attribute\Serializer;
use Spiral\Tests\Queue\Attribute\Stub\ExtendedSerializer;
use Spiral\Tests\Queue\Attribute\Stub\SerializerAttribute;
use Spiral\Tests\Queue\Attribute\Stub\WithExtendedSerializerAttribute;
use Spiral\Tests\Queue\Attribute\Stub\WithoutSerializer;

final class SerializerTest extends TestCase
{
    public static function classesProvider(): \Traversable
    {
        yield [WithoutSerializer::class, null];
        yield [SerializerAttribute::class, new Serializer('test')];
        yield [WithExtendedSerializerAttribute::class, new ExtendedSerializer()];
    }

    #[DataProvider('classesProvider')]
    public function testSerializer(string $class, ?Serializer $expected): void
    {
        $reader = (new Factory())->create();

        self::assertEquals($expected, $reader->firstClassMetadata(new \ReflectionClass($class), Serializer::class));
    }
}
