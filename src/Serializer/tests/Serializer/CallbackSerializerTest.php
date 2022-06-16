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
            static fn (mixed $payload) => $payload,
            static fn (mixed $payload) => $payload
        );

        $this->assertSame('serialize', $serializer->serialize('serialize'));
        $this->assertSame('unserialize', $serializer->unserialize('unserialize'));
    }
}
