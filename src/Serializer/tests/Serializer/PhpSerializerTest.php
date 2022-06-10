<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Serializer;

use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Serializer\PhpSerializer;

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
    }
}
