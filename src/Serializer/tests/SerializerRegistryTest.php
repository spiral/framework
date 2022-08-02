<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer;

use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Exception\SerializerNotFoundException;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\SerializerRegistry;

final class SerializerRegistryTest extends TestCase
{
    public function testRegister(): void
    {
        $registry = new SerializerRegistry();

        $this->assertFalse($registry->has('foo'));
        $this->assertFalse($registry->has('bar'));

        $registry->register('foo', new PhpSerializer());
        $registry->register('bar', new JsonSerializer());

        $this->assertTrue($registry->has('foo'));
        $this->assertTrue($registry->has('bar'));
        $this->assertInstanceOf(PhpSerializer::class, $registry->get('foo'));
        $this->assertInstanceOf(JsonSerializer::class, $registry->get('bar'));
    }

    public function testGet(): void
    {
        $registry = new SerializerRegistry();
        $registry->register('foo', new PhpSerializer());

        $this->assertInstanceOf(PhpSerializer::class, $registry->get('foo'));
        $this->expectException(SerializerNotFoundException::class);
        $registry->get('bar');
    }

    public function testHas(): void
    {
        $registry = new SerializerRegistry();

        $this->assertFalse($registry->has('foo'));
        $this->assertFalse($registry->has('bar'));

        $registry->register('foo', new PhpSerializer());
        $registry->register('bar', new JsonSerializer());

        $this->assertTrue($registry->has('foo'));
        $this->assertTrue($registry->has('bar'));
    }
}
