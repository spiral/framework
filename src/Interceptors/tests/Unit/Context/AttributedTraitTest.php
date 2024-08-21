<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Context;

use PHPUnit\Framework\TestCase;
use Spiral\Tests\Interceptors\Unit\Stub\AttributedStub;

class AttributedTraitTest extends TestCase
{
    public function testGetAttribute(): void
    {
        $dto = (new AttributedStub())
            ->withAttribute('key', 'value');

        self::assertSame('value', $dto->getAttribute('key'));
        self::assertNull($dto->getAttribute('non-exist-key'));
        // default value
        self::assertSame(42, $dto->getAttribute('non-exist-key', 42));
    }

    public function testWithAttribute(): void
    {
        $dto = new AttributedStub();

        $new = $dto->withAttribute('key', 'value');

        self::assertSame('value', $new->getAttribute('key'));
        // Immutability
        self::assertNotSame($dto, $new);
        self::assertNotSame('value', $dto->getAttribute('key'));
    }

    public function testWithAttributes(): void
    {
        $dto = new AttributedStub();

        $new = $dto
            ->withAttribute('key', 'value')
            ->withAttribute('key2', 'value2');

        self::assertSame([
            'key' => 'value',
            'key2' => 'value2',
        ], $new->getAttributes());
    }

    public function testWithoutAttributes(): void
    {
        $dto = (new AttributedStub())
            ->withAttribute('key', 'value')
            ->withAttribute('key2', 'value2');

        $new = $dto->withoutAttribute('key');

        self::assertNull($new->getAttribute('key'));
        self::assertSame([
            'key2' => 'value2',
        ], $new->getAttributes());
    }
}
