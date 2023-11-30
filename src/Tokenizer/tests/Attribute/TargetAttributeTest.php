<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Attribute;

use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Exception\SyntaxAttributeException;
use Spiral\Tests\Tokenizer\Classes\Targets\ClassWithAttributeWithArgsOnClass;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetClassWithArgs;
use Spiral\Tokenizer\Attribute\TargetAttribute;

final class TargetAttributeTest extends TestCase
{
    public function testToString(): void
    {
        $attribute = new TargetAttribute('foo');
        $this->assertSame('d8d66e598d7117a26f6268ea9780774f', (string) $attribute);

        $attribute = new TargetAttribute('foo', 'bar');
        $this->assertSame('5bd549fe54f1e987a4fbfc8513d2dc68', (string) $attribute);
    }

    public function testFilter(): void
    {
        $attribute = new TargetAttribute(attribute: WithTargetClassWithArgs::class);
        $this->assertEquals([
            ClassWithAttributeWithArgsOnClass::class,
        ], \iterator_to_array($attribute->filter([new \ReflectionClass(ClassWithAttributeWithArgsOnClass::class)])));
    }

    public function testFilterExceptionAttrWithoutNamedArgument(): void
    {
        $attribute = new TargetAttribute(attribute: WithTargetClassWithArgs::class, namedArguments: false);

        $this->expectException(SyntaxAttributeException::class);
        \iterator_to_array($attribute->filter([new \ReflectionClass(ClassWithAttributeWithArgsOnClass::class)]));
    }
}
