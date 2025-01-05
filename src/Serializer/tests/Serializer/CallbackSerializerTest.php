<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Serializer;

use PHPUnit\Framework\TestCase;
use Spiral\Serializer\Serializer\CallbackSerializer;

final class CallbackSerializerTest extends TestCase
{
    public function testSerializer(): void
    {
        $serializer = new CallbackSerializer(
            static fn (mixed $payload): mixed => $payload,
            static fn (mixed $payload): mixed => $payload
        );

        self::assertSame('serialize', $serializer->serialize('serialize'));
        self::assertSame('unserialize', $serializer->unserialize('unserialize'));
    }
}
