<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Serializer;

use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Tests\Serializer\Fixture\SomeClass;

final class PhpSerializerTest extends TestCase
{
    public function testSerializer(): void
    {
        $serializer = new PhpSerializer();

        $this->assertSame('a:2:{i:0;s:4:"some";i:1;s:8:"elements";}', $serializer->serialize(['some', 'elements']));
        $this->assertSame(['some', 'elements'], $serializer->unserialize('a:2:{i:0;s:4:"some";i:1;s:8:"elements";}'));
        $this->assertSame(['some', 'elements'], $serializer->unserialize(new class() implements \Stringable {
            public function __toString(): string
            {
                return 'a:2:{i:0;s:4:"some";i:1;s:8:"elements";}';
            }
        }));

        $object = $serializer->unserialize(
            'O:41:"Spiral\Tests\Serializer\Fixture\SomeClass":3:{s:2:"id";i:2;s:4:"text";s:4:"text";s:6:"active";b:0;}',
            SomeClass::class
        );
        $this->assertInstanceOf(SomeClass::class, $object);
        $this->assertSame(2, $object->id);
        $this->assertSame('text', $object->text);
        $this->assertFalse($object->active);
    }

    public function testUnserializeException(): void
    {
        $serializer = new PhpSerializer();

        $this->assertSame('a:2:{i:0;s:4:"some";i:1;s:8:"elements";}', $serializer->serialize(['some', 'elements']));
        $this->assertSame(['some', 'elements'], $serializer->unserialize('a:2:{i:0;s:4:"some";i:1;s:8:"elements";}'));
        $this->assertSame(
            'O:41:"Spiral\Tests\Serializer\Fixture\SomeClass":3:{s:2:"id";i:2;s:4:"text";s:4:"text";s:6:"active";b:0;}',
            $serializer->serialize(new SomeClass(2, 'text', false))
        );
        $this->assertSame(['some', 'elements'], $serializer->unserialize(new class() implements \Stringable {
            public function __toString(): string
            {
                return 'a:2:{i:0;s:4:"some";i:1;s:8:"elements";}';
            }
        }));
    }
}
