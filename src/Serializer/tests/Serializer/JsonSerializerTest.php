<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Serializer;

use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Serializer\JsonSerializer;

final class JsonSerializerTest extends TestCase
{
    public function testSerializer(): void
    {
        $serializer = new JsonSerializer();

        $this->assertSame('["some","elements"]', $serializer->serialize(['some', 'elements']));
        $this->assertSame(['some', 'elements'], $serializer->unserialize('["some","elements"]'));
        $this->assertSame(['some', 'elements'], $serializer->unserialize(new class() implements \Stringable {
            public function __toString(): string
            {
                return '["some","elements"]';
            }
        }));
    }
}
