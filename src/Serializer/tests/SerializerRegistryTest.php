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

        self::assertFalse($registry->has('foo'));
        self::assertFalse($registry->has('bar'));

        $registry->register('foo', new PhpSerializer());
        $registry->register('bar', new JsonSerializer());

        self::assertTrue($registry->has('foo'));
        self::assertTrue($registry->has('bar'));
        self::assertInstanceOf(PhpSerializer::class, $registry->get('foo'));
        self::assertInstanceOf(JsonSerializer::class, $registry->get('bar'));
    }

    public function testGet(): void
    {
        $registry = new SerializerRegistry();
        $registry->register('foo', new PhpSerializer());

        self::assertInstanceOf(PhpSerializer::class, $registry->get('foo'));
        $this->expectException(SerializerNotFoundException::class);
        $registry->get('bar');
    }

    public function testHas(): void
    {
        $registry = new SerializerRegistry();

        self::assertFalse($registry->has('foo'));
        self::assertFalse($registry->has('bar'));

        $registry->register('foo', new PhpSerializer());
        $registry->register('bar', new JsonSerializer());

        self::assertTrue($registry->has('foo'));
        self::assertTrue($registry->has('bar'));
    }
}
