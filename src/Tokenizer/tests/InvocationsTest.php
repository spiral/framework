<?php

/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Reflection\ReflectionArgument;
use Spiral\Tokenizer\Reflection\ReflectionInvocation;
use Spiral\Tokenizer\Tokenizer;

class InvocationsTest extends TestCase
{
    protected function someFunction()
    {
        $result = $this->sampleMethod('hello world');
        print_r(self::sampleMethod($result . 'plus'));
    }

    public function testInstance()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        $this->assertInstanceOf(ReflectionInvocation::class, $invocation1);
        $this->assertInstanceOf(ReflectionInvocation::class, $invocation2);
    }

    public function testClass()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        $this->assertSame(self::class, $invocation1->getClass());
        $this->assertSame(self::class, $invocation2->getClass());
    }

    public function testName()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        $this->assertSame('sampleMethod', $invocation1->getName());
        $this->assertSame('sampleMethod', $invocation2->getName());
    }

    public function testFilename()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        $this->assertSame(str_replace('\\', '/', __FILE__), $invocation1->getFilename());
        $this->assertSame(str_replace('\\', '/', __FILE__), $invocation2->getFilename());
    }

    public function testLine()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        $this->assertSame(21, $invocation1->getLine());
        $this->assertSame(22, $invocation2->getLine());
    }

    public function testLevel()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        $this->assertSame(0, $invocation1->getLevel());
        $this->assertSame(1, $invocation2->getLevel());
    }

    public function testOperator()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        $this->assertSame('->', $invocation1->getOperator());
        $this->assertSame('::', $invocation2->getOperator());
    }

    public function testIsMethod()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        $this->assertTrue($invocation1->isMethod());
        $this->assertTrue($invocation2->isMethod());
    }

    public function testCountArguments()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        $this->assertSame(1, $invocation1->countArguments());
        $this->assertSame(1, $invocation2->countArguments());
    }

    public function testSimpleArgument()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];

        $argument = $invocation1->getArgument(0);

        $this->assertInstanceOf(ReflectionArgument::class, $argument);

        $this->assertSame(ReflectionArgument::STRING, $argument->getType());
        $this->assertSame("'hello world'", $argument->getValue());
        $this->assertSame("hello world", $argument->stringValue());
    }

    public function testVariableArgument()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation2 = $invocations[1];

        $argument = $invocation2->getArgument(0);

        $this->assertInstanceOf(ReflectionArgument::class, $argument);

        $this->assertSame(ReflectionArgument::EXPRESSION, $argument->getType());
        $this->assertSame('$result.\'plus\'', $argument->getValue());
    }

    public function testSource()
    {
        $invocations = $this->getInvocations();
        $this->assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        $this->assertSame('$this->sampleMethod(\'hello world\')', $invocation1->getSource());
        $this->assertSame('self::sampleMethod($result . \'plus\')', $invocation2->getSource());
    }

    protected static function sampleMethod(string $string)
    {
    }

    /**
     * @return ReflectionInvocation[]
     * @throws \ReflectionException
     */
    protected function getInvocations(): array
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude'     => []
        ]));

        $locator = $tokenizer->invocationLocator();

        $method = new \ReflectionMethod($this, 'sampleMethod');
        $method->setAccessible(true);

        return $locator->getInvocations($method);
    }
}
