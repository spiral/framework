<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Serializer;

use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Exception\InvalidArgumentException;
use Spiral\Serializer\Exception\UnserializeException;
use Spiral\Serializer\Serializer\JsonSerializer;

final class JsonSerializerTest extends TestCase
{
    public function testSerializer(): void
    {
        $serializer = new JsonSerializer();

        self::assertSame('["some","elements"]', $serializer->serialize(['some', 'elements']));
        self::assertSame(['some', 'elements'], $serializer->unserialize('["some","elements"]'));
        self::assertSame(['some', 'elements'], $serializer->unserialize(new class implements \Stringable {
            public function __toString(): string
            {
                return '["some","elements"]';
            }
        }));
    }

    public function testObjectPassedException(): void
    {
        $serializer = new JsonSerializer();

        $this->expectException(InvalidArgumentException::class);
        $serializer->unserialize('', \stdClass::class);
    }

    public function testBadPayloadException(): void
    {
        $serializer = new JsonSerializer();

        $this->expectException(UnserializeException::class);
        $serializer->unserialize('["some","elements');
    }
}
