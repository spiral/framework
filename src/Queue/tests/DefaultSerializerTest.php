<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\Queue\DefaultSerializer;

final class DefaultSerializerTest extends TestCase
{
    /** @var DefaultSerializer */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = new DefaultSerializer();
    }

    /**
     * @requires PHP < 8.1
     */
    public function testSerialize(): void
    {
        $object = new \stdClass();
        $object->foo = 'bar';

        $serializedPayload = $this->serializer->serialize([
            'int' => 1,
            'string' => 'foo',
            'array' => ['foo'],
            'object' => $object,
            'closure' => function () use ($object) {
                return $object;
            },
        ]);

        $this->assertIsArray(
            $payload = $this->serializer->deserialize($serializedPayload)
        );

        $this->assertSame(1, $payload['int']);
        $this->assertSame('foo', $payload['string']);
        $this->assertInstanceOf(get_class($object), $payload['object']);
        $this->assertTrue($payload['closure'] instanceof \Closure);
    }
}
