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
        $this->assertSame('9100b0ad85d53cbd664bd25829f4a91a', (string) $attribute);

        $attribute = new TargetAttribute('foo', 'bar');
        $this->assertSame('c53a8e8717d2ffec906b861df6bbfad0', (string) $attribute);
    }
}