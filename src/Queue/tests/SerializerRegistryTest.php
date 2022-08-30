<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\Queue\DefaultSerializer;
use Spiral\Queue\PhpSerializer;
use PHPUnit\Framework\TestCase;
use Spiral\Queue\SerializerRegistry;

final class SerializerRegistryTest extends TestCase
{
    public function testSerialize(): void
    {
        $registry = new SerializerRegistry(new PhpSerializer());

        $object = new \stdClass();
        $object->foo = 'bar';

        $serialized = $registry->serialize([
            'int' => 1,
            'string' => 'foo',
            'array' => ['foo'],
            'object' => $object,
        ]);

        $this->assertSame(
            'a:4:{s:3:"int";i:1;s:6:"string";s:3:"foo";s:5:"array";a:1:{i:0;s:3:"foo";}s:6:"object";O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
            $serialized
        );
    }

    public function testDeserialize(): void
    {
        $registry = new SerializerRegistry(new PhpSerializer());

        $object = new \stdClass();
        $object->foo = 'bar';

        $serialized = 'a:4:{s:3:"int";i:1;s:6:"string";s:3:"foo";s:5:"array";a:1:{i:0;s:3:"foo";}s:6:"object";O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}}';

        $this->assertIsArray(
            $payload = $registry->deserialize($serialized)
        );

        $this->assertSame(1, $payload['int']);
        $this->assertSame('foo', $payload['string']);
        $this->assertInstanceOf(\get_class($object), $payload['object']);
    }

    public function testGetSerializer(): void
    {
        $registry = new SerializerRegistry(new PhpSerializer());

        $registry->addSerializer('foo', new DefaultSerializer());

        $this->assertInstanceOf(DefaultSerializer::class, $registry->getSerializer('foo'));
        $this->assertInstanceOf(PhpSerializer::class, $registry->getSerializer('bar'));
    }

    public function testAddSerializer(): void
    {
        $registry = new SerializerRegistry(new PhpSerializer());

        $this->assertInstanceOf(PhpSerializer::class, $registry->getSerializer('foo'));

        $registry->addSerializer('foo', new DefaultSerializer());
        $this->assertInstanceOf(DefaultSerializer::class, $registry->getSerializer('foo'));
    }

    public function testHasSerializer(): void
    {
        $registry = new SerializerRegistry(new PhpSerializer());

        $this->assertFalse($registry->hasSerializer('foo'));

        $registry->addSerializer('foo', new DefaultSerializer());

        $this->assertTrue($registry->hasSerializer('foo'));
    }
}
