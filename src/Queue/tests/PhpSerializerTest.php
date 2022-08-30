<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\Queue\PhpSerializer;

final class PhpSerializerTest extends TestCase
{
    public function testSerializer(): void
    {
        $serializer = new PhpSerializer();

        $object = new \stdClass();
        $object->foo = 'bar';

        $serializedPayload = $serializer->serialize([
            'int' => 1,
            'string' => 'foo',
            'array' => ['foo'],
            'object' => $object,
        ]);

        $this->assertIsArray(
            $payload = $serializer->deserialize($serializedPayload)
        );

        $this->assertSame(1, $payload['int']);
        $this->assertSame('foo', $payload['string']);
        $this->assertInstanceOf(get_class($object), $payload['object']);
    }
}
