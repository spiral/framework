<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Serializer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Exception\InvalidArgumentException;
use Spiral\Serializer\Serializer\ProtoSerializer;
use Spiral\Tests\Serializer\Fixture\PingRequest;

final class ProtoSerializerTest extends TestCase
{
    private ProtoSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ProtoSerializer();
    }

    public function testSerializeMessage(): void
    {
        $message = new PingRequest(['url' => 'foo']);

        self::assertSame($message->serializeToString(), $this->serializer->serialize($message));
    }

    public function testUnserialize(): void
    {
        $message = new PingRequest(['url' => 'foo']);

        self::assertEquals($message, $this->serializer->unserialize($message->serializeToString(), PingRequest::class));

        self::assertEquals($message, $this->serializer->unserialize($message->serializeToString(), new PingRequest()));
    }

    public function testInvalidPayloadException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->serializer->serialize('foo');
    }

    #[DataProvider('invalidTypeDataProvider')]
    public function testUnserializeInvalidArgumentException(mixed $type): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->serializer->unserialize('serialized', $type);
    }

    public static function invalidTypeDataProvider(): iterable
    {
        yield [null];
        yield ['foo'];
        yield [new \stdClass()];
        yield [\stdClass::class];
    }
}
