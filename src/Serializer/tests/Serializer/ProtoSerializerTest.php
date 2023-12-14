<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Serializer;

use Google\Protobuf\Internal\Message;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Exception\InvalidArgumentException;
use Spiral\Serializer\Serializer\ProtoSerializer;

final class ProtoSerializerTest extends TestCase
{
    public function testSerialize(): void
    {
        $serializer = new ProtoSerializer();

        $payload = $this->createMock(Message::class);
        $payload->expects($this->once())->method('serializeToString')->willReturn('serialized');

        $this->assertSame('serialized', $serializer->serialize($payload));
    }

    public function testInvalidPayloadException(): void
    {
        $serializer = new ProtoSerializer();

        $this->expectException(InvalidArgumentException::class);
        $serializer->serialize('foo');
    }

    #[DataProvider('invalidTypeDataProvider')]
    public function testUnserializeInvalidArgumentException(mixed $type): void
    {
        $serializer = new ProtoSerializer();

        $this->expectException(InvalidArgumentException::class);
        $serializer->unserialize('serialized', $type);
    }

    public static function invalidTypeDataProvider(): iterable
    {
        yield [null];
        yield ['foo'];
        yield [new \stdClass()];
        yield [\stdClass::class];
    }
}
