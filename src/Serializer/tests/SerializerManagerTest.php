<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer;

use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Exception\SerializerNotFoundException;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\SerializerRegistry;
use Spiral\Serializer\SerializerManager;

final class SerializerManagerTest extends TestCase
{
    private SerializerManager $serializer;

    protected function setUp(): void
    {
        $this->serializer = new SerializerManager(new SerializerRegistry([
            'serialize' => new PhpSerializer(),
            'json' => new JsonSerializer(),
        ]), 'json');
    }

    public function testGetSerializer(): void
    {
        $this->assertInstanceOf(PhpSerializer::class, $this->serializer->getSerializer('serialize'));
        $this->assertInstanceOf(JsonSerializer::class, $this->serializer->getSerializer('json'));

        // default serializer
        $this->assertInstanceOf(JsonSerializer::class, $this->serializer->getSerializer());

        $this->expectException(SerializerNotFoundException::class);
        $this->serializer->getSerializer('bad');
    }

    /** @dataProvider serializeDataProvider */
    public function testSerialize(mixed $payload, string $expected, ?string $format = null): void
    {
        $this->assertSame($expected, $this->serializer->serialize($payload, $format));
    }

    public function testBadSerializer(): void
    {
        $this->expectException(SerializerNotFoundException::class);
        $this->serializer->serialize('payload', 'bad');

        $this->expectException(SerializerNotFoundException::class);
        $this->serializer->unserialize('payload', 'bad');
    }

    /** @dataProvider unserializeDataProvider */
    public function testUnserialize(string|\Stringable $payload, mixed $expected, ?string $format = null): void
    {
        $this->assertSame($expected, $this->serializer->unserialize($payload, format: $format));
    }

    public function serializeDataProvider(): \Traversable
    {
        yield [['some', 'elements'], '["some","elements"]', 'json'];
        yield [['some', 'elements'], 'a:2:{i:0;s:4:"some";i:1;s:8:"elements";}', 'serialize'];
        yield [['some', 'elements'], '["some","elements"]'];
    }

    public function unserializeDataProvider(): \Traversable
    {
        yield ['["some","elements"]', ['some', 'elements'], 'json'];
        yield [new class() implements \Stringable {
            public function __toString(): string
            {
                return '["some","elements"]';
            }
        }, ['some', 'elements'], 'json'];
        yield ['a:2:{i:0;s:4:"some";i:1;s:8:"elements";}', ['some', 'elements'], 'serialize'];
        yield [new class() implements \Stringable {
            public function __toString(): string
            {
                return 'a:2:{i:0;s:4:"some";i:1;s:8:"elements";}';
            }
        }, ['some', 'elements'], 'serialize'];
        yield ['["some","elements"]', ['some', 'elements']];
    }
}
