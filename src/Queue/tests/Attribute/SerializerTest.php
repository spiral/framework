<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Factory;
use Spiral\Queue\Attribute\Serializer;
use Spiral\Tests\Queue\Attribute\Stub\ExtendedSerializer;
use Spiral\Tests\Queue\Attribute\Stub\SerializerAnnotation;
use Spiral\Tests\Queue\Attribute\Stub\SerializerAttribute;
use Spiral\Tests\Queue\Attribute\Stub\WithExtendedSerializerAnnotation;
use Spiral\Tests\Queue\Attribute\Stub\WithExtendedSerializerAttribute;
use Spiral\Tests\Queue\Attribute\Stub\WithoutSerializer;

final class SerializerTest extends TestCase
{
    #[DataProvider('classesProvider')]
    public function testSerializer(string $class, ?Serializer $expected): void
    {
        $reader = (new Factory())->create();

        $this->assertEquals($expected, $reader->firstClassMetadata(new \ReflectionClass($class), Serializer::class));
    }

    public static function classesProvider(): \Traversable
    {
        yield [WithoutSerializer::class, null];
        yield [SerializerAnnotation::class, new Serializer('test')];
        yield [SerializerAttribute::class, new Serializer('test')];
        yield [WithExtendedSerializerAnnotation::class, new ExtendedSerializer()];
        yield [WithExtendedSerializerAttribute::class, new ExtendedSerializer()];
    }
}
