<?php

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\Reflection\ReflectionArgument;
use Spiral\Tokenizer\Reflection\ReflectionFile;

class ReflectionFileTest extends TestCase
{
    public function testReflection(): void
    {
        $reflection = new ReflectionFile(__FILE__);

        self::assertContains(self::class, $reflection->getClasses());
        self::assertContains(TestTrait::class, $reflection->getTraits());
        self::assertContains(TestInterface::class, $reflection->getInterfaces());

        self::assertSame([__NAMESPACE__ . '\hello'], $reflection->getFunctions());

        $functionA = null;
        $functionB = null;

        foreach ($reflection->getInvocations() as $invocation) {
            if ($invocation->getName() == 'test_function_a') {
                $functionA = $invocation;
            }

            if ($invocation->getName() == 'test_function_b') {
                $functionB = $invocation;
            }
        }

        self::assertInstanceOf(\Spiral\Tokenizer\Reflection\ReflectionInvocation::class, $functionA);
        self::assertInstanceOf(\Spiral\Tokenizer\Reflection\ReflectionInvocation::class, $functionB);

        self::assertCount(2, $functionA->getArguments());
        self::assertSame(ReflectionArgument::VARIABLE, $functionA->getArgument(0)->getType());
        self::assertSame('$this', $functionA->getArgument(0)->getValue());

        self::assertSame(ReflectionArgument::EXPRESSION, $functionA->getArgument(1)->getType());
        self::assertSame('$a+$b', $functionA->getArgument(1)->getValue());

        self::assertSame(2, $functionB->countArguments());

        self::assertSame(ReflectionArgument::STRING, $functionB->getArgument(0)->getType());
        self::assertSame('"string"', $functionB->getArgument(0)->getValue());
        self::assertSame('string', $functionB->getArgument(0)->stringValue());

        self::assertSame(ReflectionArgument::CONSTANT, $functionB->getArgument(1)->getType());
        self::assertSame('123', $functionB->getArgument(1)->getValue());
    }

    public function testReflectionFileWithNamedParameters(): void
    {
        $reflection = new ReflectionFile(__DIR__ . '/Classes/ClassWithNamedParameter.php');

        self::assertSame([
            \Spiral\Tests\Tokenizer\Classes\ClassWithNamedParameter::class,
        ], $reflection->getClasses());
    }

    public function testReflectionFileAnonymousClass(): void
    {
        $reflection = new ReflectionFile(__DIR__ . '/Classes/ClassWithAnonymousClass.php');

        self::assertSame([
            \Spiral\Tests\Tokenizer\Classes\ClassWithAnonymousClass::class,
        ], $reflection->getClasses());
    }

    public function testReflectionFileWithHeredoc(): void
    {
        $reflection = new ReflectionFile(__DIR__ . '/Classes/ClassWithHeredoc.php');

        self::assertSame([
            'Spiral\Tests\Tokenizer\Classes\ClassWithHeredoc',
        ], $reflection->getClasses());
    }

    public function testReflectionEnum(): void
    {
        $reflection = new ReflectionFile(__DIR__ . '/Classes/ClassD.php');

        self::assertSame([
            \Spiral\Tests\Tokenizer\Classes\ClassD::class,
        ], $reflection->getEnums());
    }

    public function testReflectionTypedEnum(): void
    {
        $reflection = new ReflectionFile(__DIR__ . '/Classes/ClassE.php');

        self::assertSame([
            \Spiral\Tests\Tokenizer\Classes\ClassE::class,
        ], $reflection->getEnums());
    }

    public function testReflectionInterface(): void
    {
        $reflection = new ReflectionFile(__DIR__ . '/Interfaces/InterfaceA.php');

        self::assertSame([
            \Spiral\Tests\Tokenizer\Interfaces\InterfaceA::class,
        ], $reflection->getInterfaces());
    }

    private function deadend()
    {
        $a = $b = null;
        test_function_a($this, $a + $b);
        test_function_b("string", 123);
    }
}

function hello(): void
{
}

// phpcs:disable
trait TestTrait
{

}

interface TestInterface
{

}
