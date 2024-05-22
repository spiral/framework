<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Exception\SyntaxAttributeException;
use Spiral\Tests\Tokenizer\Classes\Targets\ClassWithAttributeWithArgsOnClass;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetClass;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetClassWithArgs;
use Spiral\Tests\Tokenizer\Interfaces\Targets\ClassExtendsClassWithAttributeOnClass;
use Spiral\Tests\Tokenizer\Interfaces\Targets\ClassImplementsInterfaceWithAttributeOnClass;
use Spiral\Tests\Tokenizer\Interfaces\Targets\InterfaceWithAttributeOnClass;
use Spiral\Tokenizer\Attribute\TargetAttribute;

final class TargetAttributeTest extends TestCase
{
    public function testToString(): void
    {
        $attribute = new TargetAttribute('foo');
        $this->assertSame('d8d66e598d7117a26f6268ea9780774f', (string)$attribute);

        $attribute = new TargetAttribute('foo', 'bar');
        $this->assertSame('5bd549fe54f1e987a4fbfc8513d2dc68', (string)$attribute);
    }

    public function testFilterAttrWithArgs(): void
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


    #[DataProvider('filterDataProvider')]
    public function testFilter(
        bool $scanParents,
        bool $found,
        string $class,
    ): void {
        $attribute = new TargetAttribute(attribute: WithTargetClass::class, scanParents: $scanParents);

        $result = \iterator_to_array($attribute->filter([new \ReflectionClass($class)]));

        $this->assertEquals(
            $found ? [$class] : [],
            $result,
        );
    }

    public static function filterDataProvider()
    {
        yield [false, true, InterfaceWithAttributeOnClass::class];
        yield [false, false, ClassExtendsClassWithAttributeOnClass::class];
        yield [false, false, ClassImplementsInterfaceWithAttributeOnClass::class];
        yield [true, true, InterfaceWithAttributeOnClass::class];
        yield [true, true, ClassExtendsClassWithAttributeOnClass::class];
        yield [true, true, ClassImplementsInterfaceWithAttributeOnClass::class];
    }
}
