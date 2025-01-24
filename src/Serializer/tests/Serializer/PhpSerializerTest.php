<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Serializer;

use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Exception\InvalidArgumentException;
use Spiral\Serializer\Exception\UnserializeException;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Tests\Serializer\Fixture\SomeClass;
use Spiral\Tests\Serializer\Fixture\SomeInterface;

final class PhpSerializerTest extends TestCase
{
    public function testSerializer(): void
    {
        $serializer = new PhpSerializer();

        self::assertSame('a:2:{i:0;s:4:"some";i:1;s:8:"elements";}', $serializer->serialize(['some', 'elements']));
        self::assertSame(['some', 'elements'], $serializer->unserialize('a:2:{i:0;s:4:"some";i:1;s:8:"elements";}'));
        self::assertSame(['some', 'elements'], $serializer->unserialize(new class implements \Stringable {
            public function __toString(): string
            {
                return 'a:2:{i:0;s:4:"some";i:1;s:8:"elements";}';
            }
        }));

        $object = $serializer->unserialize(
            'O:41:"Spiral\Tests\Serializer\Fixture\SomeClass":3:{s:2:"id";i:2;s:4:"text";s:4:"text";s:6:"active";b:0;}',
            SomeClass::class,
        );
        self::assertInstanceOf(SomeClass::class, $object);
        self::assertSame(2, $object->id);
        self::assertSame('text', $object->text);
        self::assertFalse($object->active);

        $byInterface = $serializer->unserialize(
            'O:41:"Spiral\Tests\Serializer\Fixture\SomeClass":3:{s:2:"id";i:2;s:4:"text";s:4:"text";s:6:"active";b:0;}',
            SomeInterface::class,
        );
        self::assertInstanceOf(SomeClass::class, $byInterface);
        self::assertSame(2, $byInterface->id);
        self::assertSame('text', $byInterface->text);
        self::assertFalse($byInterface->active);
    }

    public function testClassIsNotExistException(): void
    {
        $serializer = new PhpSerializer();

        $this->expectException(InvalidArgumentException::class);
        $serializer->unserialize('', 'bad');
    }

    public function testBadPayloadException(): void
    {
        $serializer = new PhpSerializer();

        $this->expectException(UnserializeException::class);
        $this->expectException(\ErrorException::class);
        $serializer->unserialize('a:2:{i:0;s:4:"some";i:1;s:8:"elements');
    }

    public function testWrongClassInTypeException(): void
    {
        $serializer = new PhpSerializer();

        $this->expectException(InvalidArgumentException::class);
        $serializer->unserialize(
            'O:41:"Spiral\Tests\Serializer\Fixture\SomeClass":3:{s:2:"id";i:2;s:4:"text";s:4:"text";s:6:"active";b:0;}',
            \stdClass::class,
        );
    }
}
