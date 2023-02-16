<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Attribute;

use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\Attribute\TargetAttribute;

final class TargetAttributeTest extends TestCase
{
    public function testToString(): void
    {
        $attribute = new TargetAttribute('foo');
        $this->assertSame('86c8823f14c6ebe7e7a801ce4050f8a4', (string) $attribute);

        $attribute = new TargetAttribute('foo', 'bar');
        $this->assertSame('11dd26b3b753e5ad457331d7699250d8', (string) $attribute);
    }
}