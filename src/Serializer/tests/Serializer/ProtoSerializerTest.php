<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Serializer;

use Google\Protobuf\Internal\Message;
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

    public function testSerialize(): void
    {
        $payload = $this->createMock(Message::class);
        $payload->expects($this->once())->method('serializeToString')->willReturn('serialized');

        $this->assertSame('serialized', $this->serializer->serialize($payload));
    }

    public function testSerializeMessage(): void
    {
        $this->assertSame(
            (new PingRequest(['url' => 'foo']))->serializeToString(),
            $this->serializer->serialize(new PingRequest(['url' => 'foo']))
        );
    }

    public function testUnserialize(): void
    {
        $message = new PingRequest(['url' => 'foo']);

        $this->assertEquals(
            $message,
            $this->serializer->unserialize($message->serializeToString(), PingRequest::class)
        );
        $this->assertEquals(
            $message,
            $this->serializer->unserialize($message->serializeToString(), new PingRequest())
        );
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
