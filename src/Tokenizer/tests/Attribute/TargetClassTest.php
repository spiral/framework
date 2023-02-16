<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Attribute;

use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\Attribute\TargetClass;

final class TargetClassTest extends TestCase
{
    public function testToString(): void
    {
        $attribute = new TargetClass('foo');
        $this->assertSame('3319d33aad20ad375d27dcd03c879454', (string) $attribute);

        $attribute = new TargetClass('foo', 'bar');
        $this->assertSame('a95c8a2cfc901290939df93183ce98d6', (string) $attribute);
    }
}