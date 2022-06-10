<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer;

use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Exception\SerializerNotFoundException;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\SerializerCollection;
use Spiral\Serializer\SerializerManager;

final class SerializerManagerTest extends TestCase
{
    public function testGetSerializer(): void
    {
        $manager = new SerializerManager(new SerializerCollection([
            'serialize' => new PhpSerializer(),
            'json' => new JsonSerializer(),
        ]));

        $this->assertInstanceOf(PhpSerializer::class, $manager->getSerializer('serialize'));
        $this->assertInstanceOf(JsonSerializer::class, $manager->getSerializer('json'));

        $this->expectException(SerializerNotFoundException::class);
        $manager->getSerializer('bad');
    }

    public function testSerialize(): void
    {
        $manager = new SerializerManager(new SerializerCollection([
            'serialize' => new PhpSerializer(),
            'json' => new JsonSerializer(),
        ]));

        $this->assertSame('["some","elements"]', $manager->serialize(['some', 'elements'], 'json'));
        $this->assertSame(['some', 'elements'], $manager->unserialize('["some","elements"]', format: 'json'));
        $this->assertSame(['some', 'elements'], $manager->unserialize(new class() implements \Stringable {
            public function __toString(): string
            {
                return '["some","elements"]';
            }
        }, format: 'json'));

        $this->assertSame(
            'a:2:{i:0;s:4:"some";i:1;s:8:"elements";}',
            $manager->serialize(['some', 'elements'], 'serialize')
        );
        $this->assertSame(
            ['some', 'elements'],
            $manager->unserialize('a:2:{i:0;s:4:"some";i:1;s:8:"elements";}', format: 'serialize'));
        $this->assertSame(
            ['some', 'elements'],
            $manager->unserialize(new class() implements \Stringable {
            public function __toString(): string
            {
                return 'a:2:{i:0;s:4:"some";i:1;s:8:"elements";}';
            }
        }, format: 'serialize'));

        $this->expectException(SerializerNotFoundException::class);
        $manager->serialize('payload', 'bad');
    }
}
