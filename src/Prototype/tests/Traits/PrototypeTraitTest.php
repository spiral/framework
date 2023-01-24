<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Traits;

use PHPUnit\Framework\TestCase;
use Spiral\Prototype\Traits\PrototypeTrait;

final class PrototypeTraitTest extends TestCase
{
    public function testDocComment(): void
    {
        $trait = new \ReflectionClass(PrototypeTrait::class);

        $this->assertStringContainsString(
            'This DocComment is auto-generated, do not edit or commit this file to repository.',
            (string) $trait->getDocComment()
        );
    }
}
