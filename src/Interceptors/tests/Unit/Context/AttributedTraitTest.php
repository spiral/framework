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

        $this->assertSame('value', $dto->getAttribute('key'));
        $this->assertNull($dto->getAttribute('non-exist-key'));
        // default value
        $this->assertSame(42, $dto->getAttribute('non-exist-key', 42));
    }

    public function testWithAttribute(): void
    {
        $dto = new AttributedStub();

        $new = $dto->withAttribute('key', 'value');

        $this->assertSame('value', $new->getAttribute('key'));
        // Immutability
        $this->assertNotSame($dto, $new);
        $this->assertNotSame('value', $dto->getAttribute('key'));
    }

    public function testWithAttributes(): void
    {
        $dto = new AttributedStub();

        $new = $dto
            ->withAttribute('key', 'value')
            ->withAttribute('key2', 'value2');

        $this->assertSame([
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

        $this->assertNull($new->getAttribute('key'));
        $this->assertSame([
            'key2' => 'value2',
        ], $new->getAttributes());
    }
}
